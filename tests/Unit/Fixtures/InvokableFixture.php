<?php

declare(strict_types=1);

namespace Unit\Fixtures;

class InvokableFixture
{
    public function __invoke(): bool
    {
        return true;
    }

    public static function staticTrue(): bool
    {
        return true;
    }

    public function instanceTrue(): bool
    {
        return true;
    }
}
