<?php

namespace Corrivate\ComposerDashboard\Model\Meta;

class PackageAliases
{
    /** @param array<string, string> $aliases */
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
