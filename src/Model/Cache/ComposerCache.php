<?php

namespace Corrivate\ComposerDashboard\Model\Cache;

use Corrivate\ComposerDashboard\Model\Value\AuditIssue;
use Corrivate\ComposerDashboard\Model\Value\RequiredPackage;
use Corrivate\ComposerDashboard\Model\Value\OutdatedPackage;
use Magento\Framework\App\Cache\Type\FrontendPool;
use Magento\Framework\Serialize\SerializerInterface;

class ComposerCache extends \Magento\Framework\Cache\Frontend\Decorator\TagScope
{

    public const TYPE_IDENTIFIER = 'corrivate_composerdashboard';
    public const CACHE_TAG = 'COMPOSERDASHBOARD_CACHE_TAG';

    private const TTL = 60 * 60 * 24;
    private const AUDIT_ISSUES = self::CACHE_TAG . '_AUDIT';
    private const OUTDATED_PACKAGES = self::CACHE_TAG . '_OUTDATED';
    private const REQUIRED_PACKAGES = self::CACHE_TAG . '_REQUIRED';

    public function __construct(
        FrontendPool                         $cacheFrontendPool,
        private readonly SerializerInterface $serializer
    ) {
        parent::__construct($cacheFrontendPool->get(self::TYPE_IDENTIFIER), self::CACHE_TAG);
    }

    /** @param AuditIssue[] $rows */
    public function saveIssues(array $rows): void
    {
        $this->save(
            $this->serializer->serialize($rows),
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

    /** @param OutdatedPackage[] $rows */
    public function saveOutdated(array $rows): void
    {
        $this->save(
            $this->serializer->serialize($rows),
            self::OUTDATED_PACKAGES,
            [ComposerCache::CACHE_TAG],
            self::TTL
        );
    }

    /** @return ?OutdatedPackage[] */
    public function loadOutdated(): ?array
    {
        $cached = $this->load(self::OUTDATED_PACKAGES);
        if ($cached !== false) {
            return array_map(
                fn ($issue) => new OutdatedPackage(...$issue),
                $this->serializer->unserialize($cached)
            );
        }

        return null;
    }

    /** @return ?RequiredPackage[] */
    public function loadRequired(): ?array
    {
        $cached = $this->load(self::REQUIRED_PACKAGES);
        if ($cached !== false) {
            return array_map(
                fn ($issue) => new RequiredPackage(...$issue),
                $this->serializer->unserialize($cached)
            );
        }

        return null;
    }

    /** @param RequiredPackage[] $rows */
    public function saveRequired(array $rows): void
    {
        $this->save(
            $this->serializer->serialize($rows),
            self::REQUIRED_PACKAGES,
            [ComposerCache::CACHE_TAG],
            self::TTL
        );
    }
}
