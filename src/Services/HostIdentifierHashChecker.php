<?php

declare(strict_types=1);

namespace Nordsec\StatusChecker\Services;

class HostIdentifierHashChecker implements StatusCheckerInterface
{
    private const NAME = 'host_identifier';

    private $critical = false;

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
        return md5(gethostname());
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
