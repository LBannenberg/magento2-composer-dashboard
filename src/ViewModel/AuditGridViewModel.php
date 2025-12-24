<?php

namespace Corrivate\ComposerDashboard\ViewModel;

use Magento\Framework\DataObject;
use Override;

class AuditGridViewModel extends \Loki\AdminComponents\Component\Grid\GridViewModel
{
    public function getCellTemplates(): array
    {
        return [
            ...parent::getCellTemplates(),
            'severity' => 'Corrivate_ComposerDashboard::grid/cell/cve-severity.phtml',
            'link' => 'Corrivate_ComposerDashboard::grid/cell/external-link.phtml',
        ];
    }
}
