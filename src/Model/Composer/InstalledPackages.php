<?php

namespace Corrivate\ComposerDashboard\Model\Composer;

use Corrivate\ComposerDashboard\Model\Cache\ComposerCache;
use Corrivate\ComposerDashboard\Model\Value\InstalledPackage;
use Symfony\Component\Process\Process;

class InstalledPackages
{
    /** @var string[] */
    private array $upgradeTypes = [];
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
    public function getOutdatedRows(bool $forceRefresh = false): array
    {
        $rows = $this->getRows($forceRefresh);
        // We only want to report on direct packages in composer.json
        $rows = array_filter($rows, fn (InstalledPackage $p) => $p->direct);

        // We want to report on packages known to be not up to date,
        // as well as abandoned packages because they will never get updates anymore
        return array_filter($rows, fn (InstalledPackage $p) => $p->latest_status != 'up-to-date' || $p->abandoned);
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
