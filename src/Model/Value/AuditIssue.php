<?php

namespace Corrivate\ComposerDashboard\Model\Value;

readonly class AuditIssue
{
    public function __construct(
        public string $package,
        public string $title,
        public string $cve,
        public string $link,
        public string $severity,
        public string $reported
    ){}
}
