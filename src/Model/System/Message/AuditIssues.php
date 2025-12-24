<?php

namespace Corrivate\ComposerDashboard\Model\System\Message;

use Corrivate\ComposerDashboard\Model\Composer\Audit;
use Corrivate\ComposerDashboard\Model\Value\AuditIssue;
use Magento\Backend\Model\UrlInterface;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Notification\MessageInterface;

class AuditIssues implements \Magento\Framework\Notification\MessageInterface
{
    public function __construct(
        private readonly AuthorizationInterface $authorization,
        private readonly Audit $audit,
        private readonly UrlInterface $urlBuilder
    ) {

    }

    public function getIdentity(): string
    {
        $issues = $this->audit->getRows();
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
            && count($this->audit->getRows()) > 0;
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
        foreach ($this->audit->getRows() as $issue) {
            $severity = min($severity, match($issue->severity) {
                'medium' => MessageInterface::SEVERITY_MAJOR,
                'high' => MessageInterface::SEVERITY_CRITICAL
            });
        }
        return $severity;
    }
}
