<?php

namespace Nordsec\StatusChecker\Services;

use Exception;
use Illuminate\Database\Capsule\Manager as Capsule;

class DatabaseChecker implements StatusCheckerInterface
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
        $capsule = new Capsule();
        $capsule->addConnection($this->configuration);

        try {
            $capsule->getConnection()->select('select 1');
        } catch (Exception $exception) {
            return StatusCheckerInterface::STATUS_FAIL;
        }

        return StatusCheckerInterface::STATUS_OK;
    }
}
