<?php

namespace Nordsec\StatusChecker\Services;

use Predis\Client as PredisClient;
use Redis as RedisClient;
use Throwable;

class RedisChecker implements StatusCheckerInterface
{
    public function __construct(
        private string $name,
        private array $configuration = [],
        private PredisClient|RedisClient|null $client = null,
        private $critical = true,
    ) {
    }

    public function setCritical(bool $critical): static
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
        try {
            switch (true) {
                case $this->client instanceof RedisClient:
                    $this->client->randomKey();
                    break;
                case $this->client instanceof PredisClient:
                    $this->client->randomkey();
                    break;
                default:
                    $cacheClient = new PredisClient(
                        $this->configuration['parameters'],
                        $this->configuration['options'],
                    );
                    $cacheClient->connect();
                    $cacheClient->randomkey();
            }
        } catch (Throwable) {
            return static::STATUS_FAIL;
        }

        return static::STATUS_OK;
    }
}
