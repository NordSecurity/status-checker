<?php

declare(strict_types=1);

namespace Nordsec\StatusChecker\Services;

class ProjectIdentifierChecker implements StatusCheckerInterface
{
    private const NAME = 'project_identifier';

    private $projectName;

    private $critical;

    public function __construct(string $projectName, bool $critical = false)
    {
        $this->projectName = $projectName;
        $this->critical = $critical;
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function needsOutput(): bool
    {
        return true;
    }

    public function checkStatus(): string
    {
        return $this->projectName;
    }

    public function isCritical(): bool
    {
        return $this->critical;
    }

    public function setCritical(bool $critical): StatusCheckerInterface
    {
        $this->critical = $critical;

        return $this;
    }
}
