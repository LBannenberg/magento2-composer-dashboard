<?php

namespace Corrivate\ComposerDashboard\Model\Composer;

use Corrivate\ComposerDashboard\Api\AuditInterface;
use Corrivate\ComposerDashboard\Model\Cache\ComposerCache;
use Corrivate\ComposerDashboard\Model\Config\Settings;
use Corrivate\ComposerDashboard\Model\Value\AuditIssue;
use Magento\Framework\Exception\LocalizedException;
use RuntimeException;
use Symfony\Component\Process\Process;

class Audit implements AuditInterface
{
    /**
     * Composer contacts every configured repository; on a large installation that
     * takes minutes. Symfony's default timeout of 60 seconds is far too low.
     */
    private const TIMEOUT = 900;

    public function __construct(
        private readonly ComposerCache $cache,
        private readonly Settings      $settings
    )
    {
    }

    /** @return AuditIssue[] */
    public function getRows(bool $forceRefresh = false): array
    {
        $issues = $this->cache->loadIssues();

        if ($issues === null || $forceRefresh) {
            // Not caught on purpose: a failed run must never be cached as "no issues".
            $issues = $this->getFromComposer();
            $this->cache->saveIssues($issues);
        }

        return $issues;
    }

    /** @return AuditIssue[] */
    private function getFromComposer(): array
    {
        $process = new Process([
            'vendor/bin/composer',
            'audit',
            '--format=json',
            '--abandoned=ignore',
            '--no-interaction',
            '--no-plugins',
            '--no-scripts',
        ]);
        $process->setWorkingDirectory(BP); // @phpstan-ignore constant.notFound
        $process->setTimeout(self::TIMEOUT);
        $process->run();

        $json = json_decode($process->getOutput(), true);

        // `composer audit` exits 1 both when advisories are found and when it fails,
        // so success is decided by the shape of the output, not by the exit code.
        if (!is_array($json) || !array_key_exists('advisories', $json)) {
            throw new RuntimeException(sprintf(
                'composer audit failed (exit code %s): %s',
                var_export($process->getExitCode(), true),
                trim($process->getErrorOutput()) ?: 'no error output'
            ));
        }

        $advisories = is_array($json['advisories']) ? $json['advisories'] : [];

        $rows = [];
        foreach ($advisories as $package => $issues) {
            if (!is_array($issues)) {
                continue;
            }
            foreach ($issues as $issue) {
                $rows[] = new AuditIssue(
                    package: (string)$package,
                    title: (string)($issue['title'] ?? '(no title)'),
                    cve: (string)($issue['cve'] ?? 'unknown'),
                    link: (string)($issue['link'] ?? ''),
                    severity: $this->matchSeverity((string)($issue['severity'] ?? 'unknown')),
                    severity_original: (string)($issue['severity'] ?? 'unknown'),
                    reported: $this->formatReportedAt($issue['reportedAt'] ?? null)
                );
            }
        }

        return $rows;
    }

    /**
     * `reportedAt` is absent from some advisory sources, and `cve` is explicitly null
     * for GitHub-only advisories, so neither may be passed to DateTime unguarded.
     */
    private function formatReportedAt(mixed $reportedAt): string
    {
        if (!is_string($reportedAt) || $reportedAt === '') {
            return '';
        }

        try {
            return (new \DateTime($reportedAt))->format('Y-m-d H:i:s');
        } catch (\Exception) {
            return '';
        }
    }

    private function matchSeverity(string $severity): int
    {
        return match ($severity) {
            'low' => AuditIssue::SEVERITY_LOW,
            'medium' => AuditIssue::SEVERITY_MEDIUM,
            'high' => AuditIssue::SEVERITY_HIGH,
            'critical' => AuditIssue::SEVERITY_CRITICAL,
            default => AuditIssue::SEVERITY_UNKNOWN
        };
    }


    /** @throws LocalizedException */
    public function getList(): array
    {
        if (!$this->settings->isApiEnabled()) {
            throw new LocalizedException(__("Composer Dashboard API is not enabled in the configuration."));
        }
        return json_decode((string)json_encode($this->getRows()), true);
    }
}
