<?php

namespace Corrivate\ComposerDashboard\Model\Composer;

use Corrivate\ComposerDashboard\Model\Cache\ComposerCache;
use Magento\Framework\Serialize\SerializerInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class Audit
{
    private const CACHE_KEY = ComposerCache::TYPE_IDENTIFIER . '_AUDIT';
    private const TTL = 60 * 60 * 24;

    public function __construct(
        private readonly ComposerCache       $cache,
        private readonly SerializerInterface $serializer
    )
    {
    }


    public function getRows(): array
    {
        // Getting the composer audit takes some time,
        // and we need these rows probably at least several times in a row,
        // so we should use caching.

        $cached = $this->cache->load(self::CACHE_KEY);

        if ($cached !== false) {
            return $this->serializer->unserialize($cached);
        }

        $fresh = $this->getFreshAudit();

        $this->cache->save(
            $this->serializer->serialize($fresh),
            self::CACHE_KEY,
            [ComposerCache::CACHE_TAG],
            self::TTL
        );

        return $fresh;
    }

    private function getFreshAudit(): array
    {
        $command = 'vendor/bin/composer audit --format=json --abandoned=ignore';
        $process = new Process(explode(' ', $command));

        $currentDir = getcwd();
        $parentDir = dirname($currentDir);
        $process->setWorkingDirectory($parentDir);

        $process->run();


        $output = $process->getOutput();
        $json = json_decode($output, true);
        $advisories = $json['advisories'] ?? [];

        $rows = [];
        foreach ($advisories as $package => $issues) {
            $rows[] = [
                'package_name' => $package,
                'issues' => 'cached at ' . (new \DateTime())->format('Y-m-d H:i:s')
            ];
        }
        return $rows;
    }
}
