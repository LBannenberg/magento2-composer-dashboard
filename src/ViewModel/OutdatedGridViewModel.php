<?php

namespace Corrivate\ComposerDashboard\ViewModel;

use Magento\Framework\DataObject;
use Override;

class OutdatedGridViewModel extends \Loki\AdminComponents\Component\Grid\GridViewModel
{
    public function getCellTemplates(): array
    {
        return [
            ...parent::getCellTemplates(),
            'abandoned' => 'Corrivate_ComposerDashboard::grid/cell/abandoned.phtml',
            'latest_status' => 'Corrivate_ComposerDashboard::grid/cell/semver-status.phtml'
        ];
    }
}
