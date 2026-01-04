<?php declare(strict_types=1);

namespace Corrivate\ComposerDashboard\Provider;

use Corrivate\ComposerDashboard\Model\Composer\InstalledPackages;
use Corrivate\ComposerDashboard\Model\Meta\PackageAliases;
use Corrivate\ComposerDashboard\Model\Value\InstalledPackage;
use Loki\AdminComponents\Grid\Column\ColumnFactory;

class InstalledPackagesArrayProvider implements \Loki\AdminComponents\Provider\ArrayProviderInterface
{
    public function __construct(
        private readonly ColumnFactory     $columnFactory,
        private readonly PackageAliases    $aliases,
        private readonly InstalledPackages $installedPackages
    ) {
    }

    public function getColumns(): array
    {
        return [
            $this->columnFactory->create([
                'code' => 'package',
                'label' => 'Package',
                'cell_template' => 'Corrivate_ComposerDashboard::grid/cell/package-name.phtml'
            ]),
            $this->columnFactory->create(['code' => 'version', 'label' => 'Current Version']),
            $this->columnFactory->create(['code' => 'release_age', 'label' => 'Age']),
            $this->columnFactory->create(['code' => 'latest', 'label' => 'Latest Version']),
            $this->columnFactory->create([
                'code' => 'semver_status',
                'label' => 'Upgrade status',
                'cell_template' => 'Corrivate_ComposerDashboard::grid/cell/semver-status.phtml'
            ]),
            $this->columnFactory->create([
                'code' => 'direct',
                'label' => 'Directly Installed',
                'cell_template' => 'Corrivate_ComposerDashboard::grid/cell/direct-package.phtml'
            ]),
            $this->columnFactory->create([
                'code' => 'abandoned',
                'label' => 'Abandoned',
                'cell_template' => 'Corrivate_ComposerDashboard::grid/cell/abandoned.phtml'
            ]),
            $this->columnFactory->create(['code' => 'description', 'label' => 'Description']),
        ];
    }

    /** @return array<array<string, mixed>> */
    public function getData(): array
    {
        return array_map(
            function (InstalledPackage $row) {
                $row = (array)$row;
                $row['package'] = $this->aliases->for($row['package']);
                return $row;
            },
            $this->installedPackages->getRows()
        );
    }
}
