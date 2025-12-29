<?php

namespace Corrivate\ComposerDashboard\Model\Composer;

class PackageAliases
{
    public function __construct(
        private readonly array $aliases = []
    )
    {
    }

    public function for(string $package): string
    {
        return isset($this->aliases[$package])
            ? "{$package} {$this->aliases[$package]}"
            : $package;
    }
}
