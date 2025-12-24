<?php declare(strict_types=1);

namespace Corrivate\ComposerDashboard\Provider;

use Corrivate\ComposerDashboard\Model\Composer\Outdated;
use Corrivate\ComposerDashboard\Model\Value\OutdatedPackage;
use Loki\AdminComponents\Grid\Column\ColumnFactory;

class OutdatedArrayProvider implements \Loki\AdminComponents\Provider\ArrayProviderInterface
{
    public function __construct(
        private readonly ColumnFactory $columnFactory,
        private readonly Outdated      $outdated
    ) {
    }

    public function getColumns(): array
    {
        return [
            $this->columnFactory->create(['code' => 'package', 'label' => 'Package']),
        ];
    }

    public function getData(): array
    {
        $rows = $this->outdated->getRows();
        return array_map(
            fn (OutdatedPackage $row) => (array)$row,
            $rows
        );
    }
}
