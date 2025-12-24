<?php

namespace Corrivate\ComposerDashboard\Model\Composer;

use Corrivate\ComposerDashboard\Model\Cache\ComposerCache;
use Corrivate\ComposerDashboard\Model\Value\OutdatedPackage;
use Symfony\Component\Process\Process;

class Outdated
{
    public function __construct(
        private readonly ComposerCache $cache
    )
    {
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
            $rows[] = new OutdatedPackage(
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
        }
        return $rows;
    }
}
