<?php declare(strict_types=1);

namespace Corrivate\ComposerDashboard\Provider;

use Loki\AdminComponents\Grid\Column\ColumnFactory;

class AuditArrayProvider implements \Loki\AdminComponents\Provider\ArrayProviderInterface
{
    public function __construct(
        private readonly ColumnFactory $columnFactory
    ){}

    public function getColumns(): array
    {
        return [
            $this->columnFactory->create(['code' => 'package_name', 'label' => 'Package']),
            $this->columnFactory->create(['code' => 'issues', 'label' => 'Issues']),
        ];
    }

    public function getData(): array
    {
        $rows = [];
        $rows[] = [
            'package_name' => 'foo/bar',
            'issues' => 'lame package'
        ];
        return $rows;
    }
}
