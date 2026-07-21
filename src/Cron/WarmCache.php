<?php

namespace Corrivate\ComposerDashboard\Cron;

use Corrivate\ComposerDashboard\Model\Composer\Audit;
use Corrivate\ComposerDashboard\Model\Composer\InstalledPackages;
use Corrivate\ComposerDashboard\Model\Config\Settings;
use Psr\Log\LoggerInterface;
use Throwable;

class WarmCache
{
    public function __construct(
        private readonly Audit             $audit,
        private readonly InstalledPackages $installedPackages,
        private readonly Settings          $settings,
        private readonly LoggerInterface   $logger
    ) {
    }

    public function execute(): void
    {
        if (!$this->settings->warmCache()) {
            return;
        }

        // Log and rethrow: the message lands in the log file, and the rethrow marks the
        // cron job as errored, so a broken composer setup is visible in both places
        // instead of silently leaving the dashboard empty.
        try {
            $this->audit->getRows();
            $this->installedPackages->getRows();
        } catch (Throwable $e) {
            $this->logger->error('Composer Dashboard cache warming failed: ' . $e->getMessage(), ['exception' => $e]);
            throw $e;
        }
    }
}
