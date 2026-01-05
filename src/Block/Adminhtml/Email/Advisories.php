<?php

namespace Corrivate\ComposerDashboard\Block\Adminhtml\Email;

use Corrivate\ComposerDashboard\Model\Composer\Audit;
use Corrivate\ComposerDashboard\Model\Value\AuditIssue;
use Magento\Framework\View\Element\Template;

class Advisories extends Template
{
    public function __construct(// @phpstan-ignore missingType.iterableValue
        private readonly Audit $audit,
        Template\Context $context,
        array $data = []
    )
    {
        parent::__construct($context, $data);
    }


    /** @return AuditIssue[] */
    public function getAdvisories(): array
    {
        return $this->audit->getRows();
    }
}
