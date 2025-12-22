<?php

namespace Corrivate\ComposerDashboard\Model\Composer;

use Corrivate\ComposerDashboard\Model\Cache\ComposerCache;
use Magento\Framework\Serialize\SerializerInterface;

class Audit
{
    private const CACHE_KEY = ComposerCache::TYPE_IDENTIFIER . '_AUDIT';
    private const TTL = 60 * 60 * 24;

    public function __construct(
        private readonly ComposerCache $cache,
        private readonly SerializerInterface $serializer
    )
    {
    }


    public function getRows(): array
    {
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
        $rows = [];
        $rows[] = [
            'package_name' => 'foo/bar',
            'issues' => 'cached at ' . (new \DateTime())->format('Y-m-d H:i:s')
        ];
        return $rows;
    }
}
