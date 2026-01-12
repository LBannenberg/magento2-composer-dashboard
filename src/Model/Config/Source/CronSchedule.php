<?php

namespace Corrivate\ComposerDashboard\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class CronSchedule implements OptionSourceInterface
{
    private const NEVER = '* * 30 2 *'; // february 30th
    private const WEEKLY = '0 3 * * 1'; // at 3AM
    private const DAILY = '0 3 * * *'; // at 3AM

    public function toOptionArray(): array
    {
        return [
            ['value' => self::DAILY, 'label' => (string)__('Daily')],
            ['value' => self::WEEKLY, 'label' => (string)__('Weekly')],
            ['value' => self::NEVER, 'label' => (string)__('Never')],
        ];
    }
}
