<?php

namespace Corrivate\ComposerDashboard\Cron;

use Corrivate\ComposerDashboard\Model\Composer\Audit;
use Corrivate\ComposerDashboard\Model\Config\Settings;

class SendAdvisoryReminders
{
    public function __construct(
        private readonly Settings $settings,
        private readonly Audit    $audit
    )
    {
    }

    public function execute(): void
    {
        $rows = $this->audit->getRows();
        if (!$rows) {
            return; // No security advisories, yay!
        }

        foreach ($this->settings->getAdvisoryRecipients() as $recipient) {
            $this->sendReminder($recipient, $rows);
        }
    }

    private function sendReminder(string $recipient, array $rows): void
    {
        // TODO
    }
}
