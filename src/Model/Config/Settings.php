<?php

namespace Corrivate\ComposerDashboard\Model\Config;

use Magento\Backend\Model\UrlInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Settings
{
    private const ADVISORY_RECIPIENTS = 'corrivate_composer_dashboard/security_advisories/recipients';
    private const OUTDATED_RECIPIENTS = 'corrivate_composer_dashboard/outdated_packages/recipients';

    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly UrlInterface $url
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

    public function getBase64Logo(): string
    {
        $path = __DIR__ . '/../../view/adminhtml/web/images/corrivate-32x32.png';
        $data = base64_encode(file_get_contents($path));
        return 'data:image/png;base64,' . $data;
    }
}
