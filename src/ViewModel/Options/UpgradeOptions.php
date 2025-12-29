<?php
declare(strict_types=1);

namespace Corrivate\ComposerDashboard\ViewModel\Options;

use Corrivate\ComposerDashboard\Model\Composer\Required;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class UpgradeOptions implements ArgumentInterface, OptionSourceInterface
{
    public function __construct(
        private readonly Required $required
    ) {
    }

    public function toOptionArray(): array
    {
        $options = [
            ['value' => '', 'label' => '']
        ];

        foreach ($this->required->getUpgradeTypes() as $type) {
            $options[] = ['value' => $type, 'label' => $type];
        }

        return $options;
    }
}
