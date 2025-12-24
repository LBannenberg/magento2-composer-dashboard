<?php

namespace Corrivate\ComposerDashboard\Model\Composer;

use Corrivate\ComposerDashboard\Model\Cache\ComposerCache;
use Corrivate\ComposerDashboard\Model\Value\AuditIssue;
use Magento\Framework\Serialize\SerializerInterface;
use Symfony\Component\Process\Process;

class Audit
{
    public function __construct(
        private readonly ComposerCache       $cache,
        private readonly SerializerInterface $serializer
    ) {
    }

    public function getRows(): array
    {
        // Getting the composer audit takes some time,
        // and we need these rows probably at least several times in a row,
        // so we should use caching.

        $issues = $this->cache->loadIssues();

        if($issues === null) {
            $issues = $this->getFreshAudit();
            $this->cache->saveIssues($issues);
        }

        return $issues;
    }

    /**
     * @return AuditIssue[]
     * @throws \Exception
     */
    private function getFreshAudit(): array
    {
        $command = 'vendor/bin/composer audit --format=json --abandoned=ignore';
        $process = new Process(explode(' ', $command));

        $currentDir = getcwd();
        $parentDir = dirname($currentDir);
        $process->setWorkingDirectory($parentDir);

        $process->run();

        $output = $process->getOutput();
        $json = json_decode($output, true);
        $advisories = $json['advisories'] ?? [];

        $rows = [];
        foreach ($advisories as $package => $issues) {
            foreach ($issues as $issue) {
                $rows[] = new AuditIssue(
                    package: $package,
                    title: $issue['title'],
                    cve: $issue['cve'],
                    link: $issue['link'],
                    severity: $issue['severity'],
                    reported: (new \DateTime($issue['reportedAt']))->format('Y-m-d H:i:s')
                );
            }
        }
        return $rows;
    }
}
