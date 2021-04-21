<?php

namespace Nordsec\StatusChecker\Services;

use Exception;
use PhpAmqpLib\Connection\AMQPSSLConnection;

class RabbitMqSslChecker implements StatusCheckerInterface
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
        $connection = new AMQPSSLConnection(
            $this->configuration['host'],
            $this->configuration['port'],
            $this->configuration['user'],
            $this->configuration['pass'],
            $this->configuration['virtual_host'],
            $this->configuration['ssl_options']
        );

        try {
            $connection->reconnect();
        } catch (Exception $exception) {
            return StatusCheckerInterface::STATUS_FAIL;
        }

        return StatusCheckerInterface::STATUS_OK;
    }
}
