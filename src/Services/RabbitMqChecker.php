<?php

namespace Nordsec\StatusChecker\Services;

use Exception;
use PhpAmqpLib\Connection\AMQPLazyConnection;

class RabbitMqChecker implements StatusCheckerInterface
{
    private $name;
    private $configuration;
    protected $critical = true;

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
        $connection = new AMQPLazyConnection(
            $this->configuration['host'],
            $this->configuration['port'],
            $this->configuration['user'],
            $this->configuration['pass'],
            $this->configuration['virtual_host']
        );

        try {
            $connection->reconnect();
        } catch (Exception $exception) {
            return StatusCheckerInterface::STATUS_FAIL;
        }

        return StatusCheckerInterface::STATUS_OK;
    }
}
