<?php

namespace Corrivate\ComposerDashboard\Model\Composer;

use Corrivate\ComposerDashboard\Model\Cache\ComposerCache;
use Corrivate\ComposerDashboard\Model\Value\InstalledPackage;
use Symfony\Component\Process\Process;

class InstalledPackages
{
    public function __construct(
        private readonly ComposerCache $cache,
    ) {
    }

    /** @return InstalledPackage[] */
    public function getRows(bool $forceFresh = false): array
    {
        $rows = $this->cache->loadInstalledPackages();

        if ($rows === null || $forceFresh) {
            $rows = $this->getFromComposer();
            $this->cache->saveInstalledPackages($rows);
        }

        return $rows;
    }

    /** @return InstalledPackage[] */
    private function getFromComposer(): array
    {
        $command = 'vendor/bin/composer show --format=json --no-dev --latest';

        $process = new Process(explode(' ', $command));
        $process->setWorkingDirectory(BP); // @phpstan-ignore constant.notFound
        $process->run();

        $output = $process->getOutput();
        $json = json_decode($output, true);
        $packages = $json['installed'] ?? [];

        $rows = [];
        foreach ($packages as $package) {
            if ($package['name'] === 'magento/product-community-edition') {
                $package['latest-status'] = $this->checkMagentoVersion($package['version'], $package['latest']);
            }

            $install = new InstalledPackage(
                package: $package['name'],
                direct: $package['direct-dependency'],
                homepage: $package['homepage'] ?? '',
                source: $package['source'] ?? '',
                version: $package['version'],
                release_age: $package['release-age'],
                release_date: $package['release-date'],
                latest: $package['latest'],
                latest_status: $package['latest-status'],
                latest_release_date: $package['latest-release-date'],
                description: $package['description'] ?? '',
                abandoned: $package['abandoned'],
                semver_status: $this->semverCodeFromComposer($package['latest-status'])
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
        preg_match('/^(\d+\.\d+\.\d+)(?:-(p\d+))?$/', $current, $currentParts);
        preg_match('/^(\d+\.\d+\.\d+)(?:-(p\d+))?$/', $latest, $latestParts);

        if ($currentParts[1] != $latestParts[1]) { // @phpstan-ignore offsetAccess.notFound, offsetAccess.notFound
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
}
