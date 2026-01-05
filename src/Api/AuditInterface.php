<?php

namespace Corrivate\ComposerDashboard\Api;


interface AuditInterface
{
    /** @return array */
    public function getList(): array;
}
