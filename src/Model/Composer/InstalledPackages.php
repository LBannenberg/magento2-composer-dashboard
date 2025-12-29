<?php

namespace Corrivate\ComposerDashboard\Model\Composer;

use Corrivate\ComposerDashboard\Model\Cache\ComposerCache;
use Corrivate\ComposerDashboard\Model\Value\InstalledPackage;
use Symfony\Component\Process\Process;

class InstalledPackages
{
    private array $upgradeTypes = [];
    public function __construct(
        private readonly ComposerCache $cache,
        private readonly PackageAliases $aliases
    ) {
    }

    /**
     * @return InstalledPackage[]
     */
    public function getRows(): array
    {
        $rows = $this->cache->loadInstalledPackages();

        if ($rows === null) {
            $rows = $this->getFromComposer();
            $this->cache->saveInstalledPackages($rows);
        }

        return $rows;
    }

    /** @return string[] */
    public function getUpgradeTypes(): array
    {
        if (!$this->upgradeTypes) {
            $this->upgradeTypes = array_unique(array_map(
                fn (InstalledPackage $row) => $row->latest_status,
                $this->getRows()
            ));
        }
        return $this->upgradeTypes;
    }

    /** @return InstalledPackage[] */
    private function getFromComposer(): array
    {
        $command = 'vendor/bin/composer show --format=json --no-dev --latest';
        $process = new Process(explode(' ', $command));

        $currentDir = getcwd();
        $parentDir = dirname($currentDir);
        $process->setWorkingDirectory($parentDir);

        $process->run();

        $output = $process->getOutput();
        $json = json_decode($output, true);
        $packages = $json['installed'] ?? [];

        $rows = [];
        foreach ($packages as $package) {
            $install = new InstalledPackage(
                package: $this->aliases->for($package['name']),
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
                abandoned: $package['abandoned']
            );
            if ($install->package === 'magento/product-community-edition') {
                $install = $this->checkMagentoVersion($install);
            }
            $rows[] = $install;
        }
        return $rows;
    }

    private function checkMagentoVersion(InstalledPackage $install): InstalledPackage
    {
        $current = explode('.', $install->version);
        $current = array_merge(
            [$current[0], $current[1]],
            explode('-', $current[2])
        );

        $latest = explode('.', $install->latest);
        $latest = array_merge(
            [$latest[0], $latest[1]],
            explode('-', $latest[2])
        );

        if ($current[0] == $latest[0]
            && $current[1] == $latest[1]
            && $current[2] == $latest[2]
        ) {
            // Then the only difference should be the -p version;
            return $install;
        }

        // A difference between for example Magento 2.4.7 and 2.4.8 is not a semver-safe-update!!!
        $data = (array)$install;
        $data['latest_status'] = 'update-possible';
        return new InstalledPackage(...$data);
    }
}
