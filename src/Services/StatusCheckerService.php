<?php

declare(strict_types=1);

namespace Nordsec\StatusChecker\Services;

class StatusCheckerService
{
    /** @var StatusCheckerInterface[] */
    private $statusCheckers;

    public function __construct(array $statusCheckers)
    {
        $this->statusCheckers = $statusCheckers;
    }

    public function getDetails(): array
    {
        $statuses = [];
        foreach ($this->statusCheckers as $statusChecker) {
            $status = $statusChecker->checkStatus();
            if ($statusChecker->needsOutput()) {
                $statuses[$statusChecker->getName()] = $status;
            }
        }

        return $statuses;
    }

    public function checkGlobalStatus(): string
    {
        foreach ($this->statusCheckers as $statusChecker) {
            if ($this->needsGlobalStatusChange($statusChecker)) {
                return $statusChecker->checkStatus();
            }
        }

        return StatusCheckerInterface::STATUS_OK;
    }

    protected function needsGlobalStatusChange(StatusCheckerInterface $statusChecker): bool
    {
        $status = $statusChecker->checkStatus();
        $isGlobalStatusChanger = in_array($status, StatusCheckerInterface::GLOBAL_STATUS_CHANGERS, true);

        return $statusChecker->isCritical() && $isGlobalStatusChanger;
    }
}
