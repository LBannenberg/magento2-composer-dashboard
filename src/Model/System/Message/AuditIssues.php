<?php

namespace Corrivate\ComposerDashboard\Model\System\Message;

use Corrivate\ComposerDashboard\Model\Cache\ComposerCache;
use Corrivate\ComposerDashboard\Model\Value\AuditIssue;
use Magento\Backend\Model\UrlInterface;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Notification\MessageInterface;

class AuditIssues implements \Magento\Framework\Notification\MessageInterface
{
    public function __construct(
        private readonly AuthorizationInterface $authorization,
        private readonly UrlInterface $urlBuilder,
        private readonly ComposerCache $composerCache
    ) {

    }

    public function getIdentity(): string
    {
        $issues = $this->composerCache->loadIssues() ?? [];
        $packages = array_map(
            fn (AuditIssue $p) => $p->package,
            $issues
        );
        // md5() here is not for cryptographic use.
        // phpcs:ignore Magento2.Security.InsecureFunction
        return md5('audit:' . implode(',', $packages));
    }

    public function isDisplayed(): bool
    {
        return $this->authorization->isAllowed('Corrivate_ComposerDashboard::composerdashboard')
            && $this->composerCache->loadIssues();
    }

    public function getText(): string
    {
        $message = __('Some of your composer packages have security issues.');
        $url = $this->urlBuilder->getUrl('composerdashboard/audit');
        $message .= ' ' . __('Please go to the <a href="%1">Composer Dashboard</a> and review them.', $url);
        return $message;
    }

    public function getSeverity(): int
    {
        $severity = MessageInterface::SEVERITY_MINOR;
        foreach (($this->composerCache->loadIssues() ?? []) as $issue) {
            $severity = min($severity, match($issue->severity) {
                'medium' => MessageInterface::SEVERITY_MAJOR,
                'high' => MessageInterface::SEVERITY_CRITICAL
            });
        }
        return $severity;
    }
}
