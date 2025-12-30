<?php

namespace Corrivate\ComposerDashboard\Cron;

use Corrivate\ComposerDashboard\Model\Composer\Audit;
use Corrivate\ComposerDashboard\Model\Composer\InstalledPackages;

class WarmCache
{
    public function __construct(
        private readonly Audit             $audit,
        private readonly InstalledPackages $installedPackages
    ) {
    }

    public function execute(): void
    {
        $this->audit->getRows();
        $this->installedPackages->getRows();
    }
}
