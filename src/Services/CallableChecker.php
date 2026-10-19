<?php

namespace Nordsec\StatusChecker\Services;

use Exception;

class CallableChecker implements StatusCheckerInterface
{
    private string $name;
    private $callback;
    private $critical = true;


    public function __construct(string $name, callable $callback)
    {
        $this->name = $name;
        $this->callback = $callback;
    }


    public function getName(): string
    {
        return $this->name;
    }

    public function needsOutput(): bool
    {
        return true;
    }

    public function checkStatus(): string
    {
        try {
            $result = call_user_func($this->callback);
        } catch (Exception $exception) {
            return static::STATUS_FAIL;
        }

        return $result ? static::STATUS_OK : static::STATUS_FAIL;
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