<?php declare(strict_types=1);

namespace Corrivate\ComposerDashboard\Provider;

use Corrivate\ComposerDashboard\Model\Composer\Outdated;
use Corrivate\ComposerDashboard\Model\Value\OutdatedPackage;
use Loki\AdminComponents\Grid\Column\ColumnFactory;

class InstalledArrayProvider implements \Loki\AdminComponents\Provider\ArrayProviderInterface
{
    public function __construct(
        private readonly ColumnFactory $columnFactory,
        private readonly Outdated      $outdated
    )
    {
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
        $rows = $this->outdated->getRows();
        return array_map(
            fn(OutdatedPackage $row) => (array)$row,
            $rows
        );
    }
}
