<?php

namespace Nordsec\StatusChecker\Services;

class MaintenanceChecker implements StatusCheckerInterface
{
    const MAINTENANCE_NAME = 'maintenance';
    const MAINTENANCE_FILENAME = '/maintenance';

    private $name;

    private $fileName;

    private $critical = true;

    public function __construct(
        string $name = self::MAINTENANCE_NAME,
        string $fileName = self::MAINTENANCE_FILENAME
    ) {
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
        return false;
    }

    public function checkStatus(): string
    {
        return file_exists($this->fileName)
            ? StatusCheckerInterface::STATUS_MAINTENANCE
            : StatusCheckerInterface::STATUS_OK;
    }
}
