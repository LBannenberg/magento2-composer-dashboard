<?php

namespace Corrivate\ComposerDashboard\Model\Value;

readonly class InstalledPackage
{
    public const SEMVER_UP_TO_DATE = 4;
    public const SEMVER_SAFE_UPDATE = 3;
    public const SEMVER_UPDATE_POSSIBLE = 2;
    public const SEMVER_UNKNOWN = 1;
    public const SEMVER_LABELS = [
        self::SEMVER_UP_TO_DATE => 'up to date',
        self::SEMVER_SAFE_UPDATE => 'minor update',
        self::SEMVER_UPDATE_POSSIBLE => 'update possible',
        self::SEMVER_UNKNOWN => 'unknown'
    ];

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
        public bool   $abandoned,
        public int    $semver_status // Using numeric codes makes this sortable
    )
    {
    }

    public function isOutdated(): bool
    {
        return $this->abandoned
            || $this->semver_status != self::SEMVER_UP_TO_DATE;
    }
}
