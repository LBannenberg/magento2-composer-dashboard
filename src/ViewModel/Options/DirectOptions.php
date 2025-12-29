<?php
declare(strict_types=1);

namespace Corrivate\ComposerDashboard\ViewModel\Options;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class DirectOptions implements ArgumentInterface, OptionSourceInterface
{
    public function toOptionArray(): array
    {
        return [
            ['value' => '', 'label' => __('')],
            ['value' => true, 'label' => __('Direct')],
            ['value' => false, 'label' => __('Indirect')]
        ];
    }
}
