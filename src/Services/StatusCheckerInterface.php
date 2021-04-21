<?php

namespace Nordsec\StatusChecker\Services;

interface StatusCheckerInterface
{
    const GLOBAL_STATUS_CHANGERS = [
        StatusCheckerInterface::STATUS_FAIL,
        StatusCheckerInterface::STATUS_MAINTENANCE,
    ];

    const STATUS_OK = 'OK';
    const STATUS_FAIL = 'FAIL';
    const STATUS_MAINTENANCE = 'MAINTENANCE';

    public function getName(): string;

    public function needsOutput(): bool;

    public function checkStatus(): string;

    public function isCritical(): bool;

    public function setCritical(bool $critical): self;
}
