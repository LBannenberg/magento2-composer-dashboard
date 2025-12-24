<?php

namespace Corrivate\ComposerDashboard\Model\Composer;

use Corrivate\ComposerDashboard\Model\Cache\ComposerCache;
use Corrivate\ComposerDashboard\Model\Value\OutdatedPackage;
use Symfony\Component\Process\Process;

class Outdated
{
    public function __construct(
        private readonly ComposerCache $cache
    ) {
    }

    public function getRows(): array
    {
        // Getting the composer outdated packages takes some time,
        // and we need these rows probably at least several times in a row,
        // so we should use caching.

        $rows = $this->cache->loadOutdated();

        if ($rows === null) {
            $rows = $this->getFromComposer();
            $this->cache->saveOutdated($rows);
        }

        return $rows;
    }

    /** @return OutdatedPackage[] */
    private function getFromComposer(): array
    {
        $command = 'vendor/bin/composer outdated --format=json --direct --no-dev';
        $process = new Process(explode(' ', $command));

        $currentDir = getcwd();
        $parentDir = dirname($currentDir);
        $process->setWorkingDirectory($parentDir);

        $process->run();

        $output = $process->getOutput();
        $json = json_decode($output, true);
        $installed = $json['installed'] ?? [];

        $rows = [];
        foreach ($installed as $package) {
            $outdated = new OutdatedPackage(
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
                description: $package['description'],
                abandoned: $package['abandoned']
            );
            if ($outdated->package === 'magento/product-community-edition') {
                $outdated = $this->checkMagentoVersion($outdated);
            }
            $rows[] = $outdated;
        }
        return $rows;
    }

    private function checkMagentoVersion(OutdatedPackage $outdated): OutdatedPackage
    {
        $current = explode('.', $outdated->version);
        $current = array_merge(
            [$current[0], $current[1]],
            explode('-', $current[2])
        );

        $latest = explode('.', $outdated->latest);
        $latest = array_merge(
            [$latest[0], $latest[1]],
            explode('-', $latest[2])
        );

        if ($current[0] == $latest[0]
            && $current[1] == $latest[1]
            && $current[2] == $latest[2]
        ) {
            // Then the only difference should be the -p version;
            return $outdated;
        }

        // A difference between for example Magento 2.4.7 and 2.4.8 is not a semver-safe-update!!!
        $data = (array)$outdated;
        $data['latest_status'] = 'update-possible';
        return new OutdatedPackage(...$data);
    }

    public function getNeededMagentoPatch(): ?string
    {
        foreach($this->getRows() as $outdated) {
            if ($outdated->package !== 'magento/product-community-edition') {
                continue;
            }


        }
        return null;
    }
}
