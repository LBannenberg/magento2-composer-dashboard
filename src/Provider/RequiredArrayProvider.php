<?php declare(strict_types=1);

namespace Corrivate\ComposerDashboard\Provider;

use Corrivate\ComposerDashboard\Model\Composer\Required;
use Loki\AdminComponents\Grid\Column\ColumnFactory;
use Corrivate\ComposerDashboard\Model\Value\RequiredPackage;

class RequiredArrayProvider implements \Loki\AdminComponents\Provider\ArrayProviderInterface
{
    public function __construct(
        private readonly ColumnFactory $columnFactory,
        private readonly Required $required
    ) {
    }

    public function getColumns(): array
    {
        return [
            $this->columnFactory->create(['code' => 'package', 'label' => 'Package']),
            $this->columnFactory->create(['code' => 'version', 'label' => 'Current Version']),
            $this->columnFactory->create(['code' => 'release_age', 'label' => 'Age']),
            $this->columnFactory->create(['code' => 'latest', 'label' => 'Latest Version']),
            $this->columnFactory->create([
                'code' => 'latest_status',
                'label' => 'Upgrade status',
                'cell_template' => 'Corrivate_ComposerDashboard::grid/cell/semver-status.phtml'
            ]),
//            $this->columnFactory->create([
//                'code' => 'direct',
//                'label' => 'Direct?',
//                'cell_template' => 'Corrivate_ComposerDashboard::grid/cell/direct-package.phtml'
//            ]),
            $this->columnFactory->create([
                'code' => 'abandoned',
                'label' => 'Abandoned?',
                'cell_template' => 'Corrivate_ComposerDashboard::grid/cell/abandoned.phtml'
            ]),
            $this->columnFactory->create(['code' => 'description', 'label' => 'Description']),
        ];
    }

    public function getData(): array
    {
        $rows = $this->required->getRows();
        return array_map(
            fn (RequiredPackage $row) => (array)$row,
            $rows
        );
    }
}
