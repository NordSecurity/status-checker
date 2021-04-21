<?php

declare(strict_types=1);

namespace Nordsec\StatusChecker\Services;

class NonCriticalRabbitMqChecker extends RabbitMqChecker
{
    protected $critical = false;
}
