<?php declare(strict_types=1);

namespace Corrivate\ComposerDashboard\Provider;

use Corrivate\ComposerDashboard\Model\Composer\Audit;
use Loki\AdminComponents\Grid\Column\ColumnFactory;

class AuditArrayProvider implements \Loki\AdminComponents\Provider\ArrayProviderInterface
{
    public function __construct(
        private readonly ColumnFactory $columnFactory,
        private readonly Audit $audit
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
        return $this->audit->getRows();
    }
}
