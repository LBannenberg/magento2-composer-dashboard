<?php

namespace Corrivate\ComposerDashboard\Model\Composer;

use Corrivate\ComposerDashboard\Api\InstalledPackagesInterface;
use Corrivate\ComposerDashboard\Model\Cache\ComposerCache;
use Corrivate\ComposerDashboard\Model\Config\Settings;
use Corrivate\ComposerDashboard\Model\Value\InstalledPackage;
use Magento\Framework\Exception\LocalizedException;
use RuntimeException;
use Symfony\Component\Process\Process;

class InstalledPackages implements InstalledPackagesInterface
{
    /**
     * `show --latest` queries every configured repository. Measured at 149s on an
     * installation with ~690 packages across 15 repositories; the Symfony default of
     * 60s guarantees a ProcessTimedOutException there.
     */
    private const TIMEOUT = 600; // 10m

    public function __construct(
        private readonly ComposerCache $cache,
        private readonly Settings      $settings
    ) {
    }

    /** @return InstalledPackage[] */
    public function getRows(bool $forceFresh = false): array
    {
        $rows = $this->cache->loadInstalledPackages();

        if ($rows === null || $forceFresh) {
            // Not caught on purpose: a failed run must never be cached as an empty list.
            $rows = $this->getFromComposer();
            $this->cache->saveInstalledPackages($rows);
        }

        return $rows;
    }

    /** @return InstalledPackage[] */
    private function getFromComposer(): array
    {
        $process = new Process([
            'vendor/bin/composer',
            'show',
            '--format=json',
            '--no-dev',
            '--latest',
            '--no-interaction',
            '--no-plugins',
            '--no-scripts',
            '--ignore-platform-req=php' // to ensure we really get to see the latest
        ]);
        $process->setWorkingDirectory(BP); // @phpstan-ignore constant.notFound
        $process->setTimeout(self::TIMEOUT);
        $process->run();

        $json = json_decode($process->getOutput(), true);

        // A failed run produces empty stdout, which must be told apart from a
        // successful run: silently returning [] would be cached as "nothing installed".
        if (!is_array($json) || !array_key_exists('installed', $json)) {
            throw new RuntimeException(sprintf(
                'composer show failed (exit code %s): %s',
                var_export($process->getExitCode(), true),
                trim($process->getErrorOutput()) ?: 'no error output'
            ));
        }

        $packages = is_array($json['installed']) ? $json['installed'] : [];

        $rows = [];
        foreach ($packages as $package) {
            if (($package['name'] ?? null) === 'magento/product-community-edition') {
                $package['latest-status'] = $this->checkMagentoVersion(
                    (string)$package['version'],
                    (string)$package['latest']
                );
            }

            // `abandoned` is `true` OR the replacement package name (ShowCommand.php),
            // so it must be cast rather than passed straight into a bool parameter.
            $install = new InstalledPackage(
                package: (string)$package['name'],
                direct: (bool)($package['direct-dependency'] ?? false),
                homepage: (string)($package['homepage'] ?? ''),
                source: (string)($package['source'] ?? ''),
                version: (string)$package['version'],
                release_age: (string)($package['release-age'] ?? ''),
                release_date: (string)($package['release-date'] ?? ''),
                latest: (string)($package['latest'] ?? ''),
                latest_status: (string)($package['latest-status'] ?? 'unknown'),
                latest_release_date: (string)($package['latest-release-date'] ?? ''),
                description: (string)($package['description'] ?? ''),
                abandoned: (bool)($package['abandoned'] ?? false),
                semver_status: $this->semverCodeFromComposer((string)($package['latest-status'] ?? 'unknown'))
            );

            $rows[] = $install;
        }
        return $rows;
    }

    private function checkMagentoVersion(string $current, string $latest): string
    {
        if ($current === $latest) {
            return 'up-to-date';
        }

        // Split the version tags into a #.#.# version part and optional -p# part
        $currentMatched = preg_match('/^(\d+\.\d+\.\d+)(?:-(p\d+))?$/', $current, $currentParts);
        $latestMatched = preg_match('/^(\d+\.\d+\.\d+)(?:-(p\d+))?$/', $latest, $latestParts);

        // Pre-release tags such as 2.4.9-beta1 do not match; without this guard the
        // reads below emit "Undefined array key 1" warnings and compare null to null.
        if ($currentMatched !== 1 || $latestMatched !== 1) {
            return 'unknown';
        }

        if ($currentParts[1] != $latestParts[1]) {
            // Then this is more than a patch-level difference and needs significant testing during upgrade
            return 'update-possible';
        }

        if (($currentParts[2] ?? '') != ($latestParts[2] ?? '')) {
            // Only difference is at a patch level
            return 'semver-safe-update';
        }

        // One of the version strings must be quite weird
        return 'unknown';
    }

    private function semverCodeFromComposer(string $latestStatus): int
    {
        return match($latestStatus) {
            'up-to-date' => InstalledPackage::SEMVER_UP_TO_DATE,
            'semver-safe-update' => InstalledPackage::SEMVER_SAFE_UPDATE,
            'update-possible' => InstalledPackage::SEMVER_UPDATE_POSSIBLE,
            default => InstalledPackage::SEMVER_UNKNOWN
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
