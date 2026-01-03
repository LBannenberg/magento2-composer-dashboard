<?php

namespace Corrivate\ComposerDashboard\Block\Email;

use Corrivate\ComposerDashboard\Model\Composer\InstalledPackages;
use Corrivate\ComposerDashboard\Model\Value\InstalledPackage;
use Magento\Framework\View\Element\Template;

class Outdated extends Template
{
    public function __construct(
        private readonly InstalledPackages $packages,
        Template\Context $context,
        array $data = []
    )
    {
        parent::__construct($context, $data);
    }


    /** @return InstalledPackage[] */
    public function getOutdated(): array
    {
        return $this->packages->getOutdatedRows();
    }
}
