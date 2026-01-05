<?php

namespace Corrivate\ComposerDashboard\Setup\Patch\Data;

use Magento\Framework\App\Cache\Manager;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class EnableCustomCache implements DataPatchInterface
{

    public function __construct(
        private readonly Manager $cacheManager
    ) {
    }

    public function apply(): EnableCustomCache
    {
        $this->cacheManager->setEnabled(['corrivate_composerdashboard'], true);
        return $this;
    }

    public static function getDependencies(): array
    {
        return [];
    }

    public function getAliases(): array
    {
        return [];
    }
}
