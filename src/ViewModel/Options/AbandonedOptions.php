<?php
declare(strict_types=1);

namespace Corrivate\ComposerDashboard\ViewModel\Options;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class AbandonedOptions implements ArgumentInterface, OptionSourceInterface
{
    public function toOptionArray(): array
    {
        return [
            ['value' => '', 'label' => __('')],
            ['value' => true, 'label' => __('Abandoned')],
            ['value' => false, 'label' => __('No')]
        ];
    }
}
