<?php

namespace Corrivate\ComposerDashboard\Model\Composer;

use Corrivate\ComposerDashboard\Model\Cache\ComposerCache;
use Corrivate\ComposerDashboard\Model\Value\AuditIssue;
use Symfony\Component\Process\Process;

class Audit
{
    public function __construct(
        private readonly ComposerCache $cache
    ) {
    }

    /** @return AuditIssue[] */
    public function getRows(bool $forceRefresh = false): array
    {
        $issues = $this->cache->loadIssues();

        if ($issues === null || $forceRefresh) {
            $issues = $this->getFromComposer();
            $this->cache->saveIssues($issues);
        }

        return $issues;
    }

    /** @return AuditIssue[] */
    private function getFromComposer(): array
    {
        $command = 'vendor/bin/composer audit --format=json --abandoned=ignore';

        $process = new Process(explode(' ', $command));
        $process->setWorkingDirectory(BP); // @phpstan-ignore constant.notFound
        $process->run();

        $output = $process->getOutput();
        $json = json_decode($output, true);
        $advisories = $json['advisories'] ?? [];

        $rows = [];
        foreach ($advisories as $package => $issues) {
            foreach ($issues as $issue) {
                $rows[] = new AuditIssue(
                    package: $package,
                    title: $issue['title'] ?? '(no title)',
                    cve: $issue['cve'] ?? 'unknown',
                    link: $issue['link'] ?? '',
                    severity: $this->matchSeverity($issue['severity'] ?? 'unknown'),
                    reported: (new \DateTime($issue['reportedAt']))->format('Y-m-d H:i:s')
                );
            }
        }

        return $rows;
    }

    private function matchSeverity(string $severity): int
    {
        return match($severity) {
            'low' => AuditIssue::SEVERITY_LOW,
            'medium' => AuditIssue::SEVERITY_MEDIUM,
            'high' => AuditIssue::SEVERITY_HIGH,
            'critical' => AuditIssue::SEVERITY_CRITICAL,
            default => AuditIssue::SEVERITY_UNKNOWN
        };
    }
}
