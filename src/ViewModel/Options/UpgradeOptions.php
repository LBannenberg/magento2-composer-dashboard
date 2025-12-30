<?php
declare(strict_types=1);

namespace Corrivate\ComposerDashboard\ViewModel\Options;

use Corrivate\ComposerDashboard\Model\Composer\InstalledPackages;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class UpgradeOptions implements ArgumentInterface, OptionSourceInterface
{
    public function __construct(
        private readonly InstalledPackages $installedPackages
    ) {
    }

    /** @return array<array{value: string, label: string}> */
    public function toOptionArray(): array
    {
        $options = [
            ['value' => '', 'label' => '']
        ];

        foreach ($this->installedPackages->getUpgradeTypes() as $type) {
            $options[] = ['value' => $type, 'label' => $type];
        }

        return $options;
    }
}
