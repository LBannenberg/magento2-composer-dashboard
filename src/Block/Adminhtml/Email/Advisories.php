<?php

namespace Corrivate\ComposerDashboard\Block\Adminhtml\Email;

use Corrivate\ComposerDashboard\Model\Composer\Audit;
use Corrivate\ComposerDashboard\Model\Config\Settings;
use Corrivate\ComposerDashboard\Model\Value\AuditIssue;
use Magento\Framework\View\Element\Template;

class Advisories extends Template
{
    public function __construct(// @phpstan-ignore missingType.iterableValue
        private readonly Audit $audit,
        private readonly Settings $settings,
        Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /** @return AuditIssue[] */
    public function getAdvisories(): array
    {
        $rows = $this->audit->getRows();
        $ignored = $this->settings->getIgnoredAdvisories();
        $rows = array_filter($rows, fn (AuditIssue $row) => !in_array($row->package, $ignored));
        usort($rows, fn (AuditIssue $a, AuditIssue $b) => $a->severity <=> $b->severity);
        return $rows;
    }
}
