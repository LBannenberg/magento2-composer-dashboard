<?php

namespace Corrivate\ComposerDashboard\Model\Value;

readonly class AuditIssue
{
    public const SEVERITY_UNKNOWN = 1;
    public const SEVERITY_CRITICAL = 2;
    public const SEVERITY_HIGH = 3;
    public const SEVERITY_MEDIUM = 4;
    public const SEVERITY_LOW = 5;
    public const SEVERITY_LABELS = [
        self::SEVERITY_UNKNOWN => 'unknown',
        self::SEVERITY_CRITICAL => 'critical',
        self::SEVERITY_HIGH => 'high',
        self::SEVERITY_MEDIUM => 'medium',
        self::SEVERITY_LOW => 'low'
    ];

    public function __construct(
        public string $package,
        public string $title,
        public string $cve,
        public string $link,
        public int    $severity,
        public string $reported
    )
    {
    }
}
