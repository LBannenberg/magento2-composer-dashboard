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
                $package = $this->checkMagentoVersion($package);
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
                latest_release_date: $package['latest-release-date'],
                description: $package['description'] ?? '',
                abandoned: $package['abandoned'],
                semver_status: $this->semverCodeFromComposer($package)
            );

            $rows[] = $install;
        }
        return $rows;
    }

    private function checkMagentoVersion(array $package): array
    {
        $current = $package['version'];
        $latest = $package['latest'];

        if ($current === $latest) {
            return $package;
        }

        // Split the version tags into a #.#.# version part and optional -p# part
        preg_match('/^(\d+\.\d+\.\d+)(?:-(p\d+))?$/', $current, $currentParts);
        preg_match('/^(\d+\.\d+\.\d+)(?:-(p\d+))?$/', $latest, $latestParts);

        if ($currentParts[1] != $latestParts[1]) {
            // Then this is more than a patch-level difference and needs significant testing during upgrade
            $package['latest-status'] = 'update-possible';
            return $package;
        }

        if (($currentParts[2] ?? '') != ($latestParts[2] ?? '')) {
            // Only difference is at a patch level
            $package['latest-status'] = 'semver-safe-update';
            return $package;
        }

        // One of the version strings must be quite weird
        $package['latest-status'] = 'unknown';
        return $package;
    }

    private function semverCodeFromComposer(array $package): int
    {
        return match($package['latest-status'] ?? '') {
            'up-to-date' => InstalledPackage::SEMVER_UP_TO_DATE,
            'semver-safe-update' => InstalledPackage::SEMVER_SAFE_UPDATE,
            'update-possible' => InstalledPackage::SEMVER_UPDATE_POSSIBLE,
            default => InstalledPackage::SEMVER_UNKNOWN
        };
    }
}
