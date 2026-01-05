<?php

namespace Corrivate\ComposerDashboard\Api;


interface InstalledPackagesInterface
{
    /** @return array */
    public function getList(): array;
}
