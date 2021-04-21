<?php

namespace Nordsec\StatusChecker\Services;

class VersionChecker implements StatusCheckerInterface
{
    const CHECKER_NAME = 'version';

    private $homeDirectory;

    public function __construct(string $homeDirectory)
    {
        $this->homeDirectory = $homeDirectory;
    }

    public function setCritical(bool $critical): StatusCheckerInterface
    {
        return $this;
    }

    public function getName(): string
    {
        return self::CHECKER_NAME;
    }

    public function needsOutput(): bool
    {
        return true;
    }

    public function isCritical(): bool
    {
        return false;
    }

    public function checkStatus(): string
    {
        return basename($this->homeDirectory);
    }
}
