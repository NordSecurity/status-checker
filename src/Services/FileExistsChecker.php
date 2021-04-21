<?php

namespace Nordsec\StatusChecker\Services;

class FileExistsChecker implements StatusCheckerInterface
{
    private $name;

    private $fileName;

    private $critical = true;

    public function __construct(string $name, string $fileName)
    {
        $this->name = $name;
        $this->fileName = $fileName;
    }

    public function setCritical(bool $critical): StatusCheckerInterface
    {
        $this->critical = $critical;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function isCritical(): bool
    {
        return $this->critical;
    }

    public function needsOutput(): bool
    {
        return true;
    }

    public function checkStatus(): string
    {
        return file_exists($this->fileName)
            ? StatusCheckerInterface::STATUS_OK
            : StatusCheckerInterface::STATUS_FAIL;
    }
}
