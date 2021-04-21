<?php

namespace Nordsec\StatusChecker\Services;

use Exception;
use InfluxDB\Client;

class InfluxDBChecker implements StatusCheckerInterface
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
        $client = new Client(
            $this->configuration['host'],
            $this->configuration['port'],
            $this->configuration['user'],
            $this->configuration['password'],
            $this->configuration['ssl'],
            $this->configuration['verifySsl']
        );

        try {
            $database = $client->selectDB($this->configuration['db']);
            $result = $database->query('show databases');
            $points = $result->getPoints();
            if (count($points) === 0) {
                return StatusCheckerInterface::STATUS_FAIL;
            }
        } catch (Exception $exception) {
            return StatusCheckerInterface::STATUS_FAIL;
        }

        return StatusCheckerInterface::STATUS_OK;
    }
}
