<?php

namespace Corrivate\ComposerDashboard\Model\Cache;

use Corrivate\ComposerDashboard\Model\Value\AuditIssue;
use Corrivate\ComposerDashboard\Model\Value\InstalledPackage;
use Magento\Framework\App\Cache\Type\FrontendPool;
use Magento\Framework\Serialize\SerializerInterface;

class ComposerCache extends \Magento\Framework\Cache\Frontend\Decorator\TagScope
{

    public const TYPE_IDENTIFIER = 'corrivate_composerdashboard';
    public const CACHE_TAG = 'COMPOSERDASHBOARD_CACHE_TAG';

    private const TTL = 60 * 60 * 24;
    private const AUDIT_TAG = self::CACHE_TAG . '_AUDIT';
    private const INSTALLED_TAG = self::CACHE_TAG . '_INSTALLED';

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
            $this->serializer->serialize($rows), // @phpstan-ignore argument.type
            self::AUDIT_TAG,
            [ComposerCache::CACHE_TAG],
            self::TTL
        );
    }

    /** @return ?AuditIssue[] */
    public function loadIssues(): ?array
    {
        $cached = $this->load(self::AUDIT_TAG);
        if ($cached !== false) {
            return array_map(
                fn ($issue) => new AuditIssue(...$issue),
                $this->serializer->unserialize($cached) // @phpstan-ignore argument.type, argument.type
            );
        }

        return null;
    }


    /** @return ?InstalledPackage[] */
    public function loadInstalledPackages(): ?array
    {
        $cached = $this->load(self::INSTALLED_TAG);
        if ($cached !== false) {
            return array_map(
                fn ($issue) => new InstalledPackage(...$issue),
                $this->serializer->unserialize($cached) // @phpstan-ignore argument.type, argument.type
            );
        }

        return null;
    }

    /** @param InstalledPackage[] $rows */
    public function saveInstalledPackages(array $rows): void
    {
        $this->save(
            $this->serializer->serialize($rows), // @phpstan-ignore argument.type
            self::INSTALLED_TAG,
            [ComposerCache::CACHE_TAG],
            self::TTL
        );
    }
}
