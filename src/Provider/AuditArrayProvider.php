<?php declare(strict_types=1);

namespace Corrivate\ComposerDashboard\Provider;

use Corrivate\ComposerDashboard\Model\Composer\Audit;
use Corrivate\ComposerDashboard\Model\Value\AuditIssue;
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
            $this->columnFactory->create(['code' => 'package', 'label' => 'Package']),
            $this->columnFactory->create(['code' => 'severity', 'label' => 'Severity']),
            $this->columnFactory->create(['code' => 'cve', 'label' => 'CVE']),
            $this->columnFactory->create(['code' => 'reported', 'label' => 'Reported At']),
            $this->columnFactory->create(['code' => 'title', 'label' => 'Issue']),
            $this->columnFactory->create(['code' => 'link', 'label' => 'Details']),
        ];
    }

    public function getData(): array
    {
        $rows =  $this->audit->getRows();
        return array_map(
            fn(AuditIssue $row) => (array)$row,
            $rows
        );
    }
}
