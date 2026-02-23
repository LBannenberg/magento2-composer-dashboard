<?php

namespace Corrivate\ComposerDashboard\Block\Adminhtml\Email;

use Corrivate\ComposerDashboard\Model\Composer\InstalledPackages;
use Corrivate\ComposerDashboard\Model\Config\Settings;
use Corrivate\ComposerDashboard\Model\Value\InstalledPackage;
use Magento\Framework\View\Element\Template;

class Outdated extends Template
{
    public function __construct(// @phpstan-ignore missingType.iterableValue
        private readonly InstalledPackages $packages,
        private readonly Settings $settings,
        Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /** @return InstalledPackage[] */
    public function getOutdated(): array
    {
        $rows = array_filter($this->packages->getRows(), fn (InstalledPackage $r) => $r->direct && $r->isOutdated());
        $ignored = $this->settings->getIgnoredOutdated();
        $rows = array_filter($rows, fn (InstalledPackage $row) => !in_array($row->package, $ignored));
        usort($rows, fn (InstalledPackage $a, InstalledPackage $b) => $a->semver_status <=> $b->semver_status);
        return $rows;
    }
}
