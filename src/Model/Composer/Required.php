<?php

namespace Corrivate\ComposerDashboard\Model\Composer;

use Corrivate\ComposerDashboard\Model\Cache\ComposerCache;
use Corrivate\ComposerDashboard\Model\Value\RequiredPackage;
use Symfony\Component\Process\Process;

class Required
{
    public function __construct(
        private readonly ComposerCache $cache
    ) {
    }

    /**
     * @return RequiredPackage[]
     */
    public function getRows(): array
    {
        $rows = $this->cache->loadRequired();

        if ($rows === null) {
            $rows = $this->getFromComposer();
            $this->cache->saveRequired($rows);
        }

        return $rows;
    }

    /** @return RequiredPackage[] */
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
            $install = new RequiredPackage(
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

    private function checkMagentoVersion(RequiredPackage $install): RequiredPackage
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
        return new RequiredPackage(...$data);
    }
}
