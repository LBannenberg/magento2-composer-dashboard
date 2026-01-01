<?php

namespace Corrivate\ComposerDashboard\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;

class Settings
{
    private const ADVISORY_RECIPIENTS = 'corrivate_composer_dashboard/security_advisories/recipients';
    private const OUTDATED_RECIPIENTS = 'corrivate_composer_dashboard/outdated_packages/recipients';
    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig
    ) {
    }

    /** @return string[] */
    public function getAdvisoryRecipients(): array
    {
        return $this->getCommaSeparatedEmails(self::ADVISORY_RECIPIENTS);
    }

    /** @return string[] */
    public function getOutdatedRecipients(): array
    {
        return $this->getCommaSeparatedEmails(self::OUTDATED_RECIPIENTS);
    }

    /** @return string[] */
    private function getCommaSeparatedEmails(string $path): array
    {
        $value = (string)$this->scopeConfig->getValue($path);
        if (!$value) {
            return [];
        }
        $values = explode(',', $value);
        $values = array_map(fn (string $v) => trim($v), $values);
        return array_filter(
            $values,
            fn (string $v) => filter_var($v, FILTER_VALIDATE_EMAIL)
        );
    }
}
