<?php

namespace Nordsec\StatusChecker\Services;

use Exception;
use Predis\Client;

class RedisChecker implements StatusCheckerInterface
{
    private $name;

    private $configuration;

    private $critical = true;

    public function __construct($name, $configuration)
    {
        $this->name = $name;
        $this->configuration = $configuration;
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

    public function needsOutput(): bool
    {
        return true;
    }

    public function isCritical(): bool
    {
        return $this->critical;
    }

    public function checkStatus(): string
    {
        $parameters = $this->configuration['parameters'];
        $options = $this->configuration['options'];

        try {
            $cacheClient = new Client($parameters, $options);
            $cacheClient->connect();
            $cacheClient->randomkey();
        } catch (Exception $exception) {
            return StatusCheckerInterface::STATUS_FAIL;
        }

        return StatusCheckerInterface::STATUS_OK;
    }
}
