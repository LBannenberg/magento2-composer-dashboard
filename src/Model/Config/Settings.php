<?php

namespace Corrivate\ComposerDashboard\Model\Config;

use Magento\Backend\Model\UrlInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Settings
{
    private const XPATH_ADVISORY_RECIPIENTS = 'corrivate_composer_dashboard/security_advisories/recipients';
    private const XPATH_ADVISORY_IGNORED_PACKAGES = 'corrivate_composer_dashboard/security_advisories/ignored_packages';
    private const XPATH_OUTDATED_RECIPIENTS = 'corrivate_composer_dashboard/outdated_packages/recipients';
    private const XPATH_OUTDATED_IGNORED_PACKAGES = 'corrivate_composer_dashboard/outdated_packages/ignored_packages';
    private const XPATH_API_ENABLED = 'corrivate_composer_dashboard/api/enabled';

    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly UrlInterface $url
    ) {
    }

    /** @return string[] */
    public function getAdvisoryRecipients(): array
    {
        return $this->getCommaSeparatedEmails(self::XPATH_ADVISORY_RECIPIENTS);
    }

    /** @return string[] */
    public function getOutdatedRecipients(): array
    {
        return $this->getCommaSeparatedEmails(self::XPATH_OUTDATED_RECIPIENTS);
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
            fn (string $v) => filter_var($v, FILTER_VALIDATE_EMAIL) // @phpstan-ignore argument.type
        );
    }

    /** @return array{email: string, name: string} */
    public function getSender(): array
    {
        return [
            'email' => (string)$this->scopeConfig->getValue('trans_email/ident_general/email'),
            'name' => (string)$this->scopeConfig->getValue('trans_email/ident_general/name')
        ];
    }

    public function getStoreUrl(): string
    {
        return rtrim(str_replace('index.php', '', $this->url->getBaseUrl()), '/');
    }

    public function isApiEnabled(): bool
    {
        return (bool)$this->scopeConfig->getValue(self::XPATH_API_ENABLED);
    }

    /** return string[] */
    public function getIgnoredAdvisories(): array
    {
        $value = (string)$this->scopeConfig->getValue(self::XPATH_ADVISORY_IGNORED_PACKAGES);
        $value = str_replace("\n", "", $value);
        $value = str_replace(",", "\n", $value);
        $items = explode("\n", $value);
        $items = array_map(fn ($item) => trim($item), $items);
        return array_filter($items);
    }

    /** return string[] */
    public function getIgnoredOutdated(): array
    {
        $value = (string)$this->scopeConfig->getValue(self::XPATH_OUTDATED_IGNORED_PACKAGES);
        $value = str_replace("\r", "", $value);
        $value = str_replace(",", "\n", $value);
        $items = explode("\n", $value);
        $items = array_map(fn ($item) => trim($item), $items);
        return array_filter($items);
    }
}
