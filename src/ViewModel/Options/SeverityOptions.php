<?php
declare(strict_types=1);

namespace Corrivate\ComposerDashboard\ViewModel\Options;

use Corrivate\ComposerDashboard\Model\Value\AuditIssue;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class SeverityOptions implements ArgumentInterface, OptionSourceInterface
{
    /** @return array<array{value: string, label: string}> */
    public function toOptionArray(): array
    {
        $options = [
            ['value' => '', 'label' => '']
        ];

        foreach (AuditIssue::SEVERITY_LABELS as $value => $label) {
            $options[] = ['value' => $value, 'label' => $label];
        }

        return $options;
    }
}
