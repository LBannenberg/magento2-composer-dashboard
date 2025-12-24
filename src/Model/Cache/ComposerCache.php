<?php

namespace Corrivate\ComposerDashboard\Model\Cache;

use Corrivate\ComposerDashboard\Model\Value\AuditIssue;
use Magento\Framework\App\Cache\Type\FrontendPool;
use Magento\Framework\Serialize\SerializerInterface;

class ComposerCache extends \Magento\Framework\Cache\Frontend\Decorator\TagScope
{

    public const TYPE_IDENTIFIER = 'corrivate_composerdashboard';
    public const CACHE_TAG = 'COMPOSERDASHBOARD_CACHE_TAG';

    private const TTL = 60 * 60 * 24;
    private const AUDIT_ISSUES = self::CACHE_TAG . '_AUDIT';

    public function __construct(
        FrontendPool                         $cacheFrontendPool,
        private readonly SerializerInterface $serializer
    ) {
        parent::__construct($cacheFrontendPool->get(self::TYPE_IDENTIFIER), self::CACHE_TAG);
    }

    /** @param AuditIssue[] $issues */
    public function saveIssues(array $issues): void
    {
        $this->save(
            $this->serializer->serialize($issues),
            self::AUDIT_ISSUES,
            [ComposerCache::CACHE_TAG],
            self::TTL
        );
    }

    /** @return ?AuditIssue[] */
    public function loadIssues(): ?array
    {
        $cached = $this->load(self::AUDIT_ISSUES);
        if ($cached !== false) {
            return array_map(
                fn ($issue) => new AuditIssue(...$issue),
                $this->serializer->unserialize($cached)
            );
        }

        return null;
    }
}
