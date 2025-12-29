<?php

namespace Corrivate\ComposerDashboard\Model\Value;

readonly class InstalledPackage
{
    public function __construct(
        public string $package,
        public bool   $direct,
        public string $homepage,
        public string $source,
        public string $version,
        public string $release_age,
        public string $release_date,
        public string $latest,
        public string $latest_status,
        public string $latest_release_date,
        public string $description,
        public bool   $abandoned
    )
    {
    }
}
