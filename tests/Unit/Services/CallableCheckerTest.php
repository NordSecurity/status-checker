<?php

declare(strict_types=1);

namespace Unit\Services;

use Nordsec\StatusChecker\Services\CallableChecker;
use Nordsec\StatusChecker\Services\StatusCheckerInterface;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Unit\Fixtures\InvokableFixture;

class CallableCheckerTest extends TestCase
{
    public static function callableProvider(): iterable
    {
        yield 'closure returning true' => [
            static fn (): bool => true,
            StatusCheckerInterface::STATUS_OK,
        ];

        yield 'closure returning false' => [
            static fn (): bool => false,
            StatusCheckerInterface::STATUS_FAIL,
        ];

        yield 'truthy non-bool result' => [
            static fn (): int => 1,
            StatusCheckerInterface::STATUS_OK,
        ];

        yield 'falsy non-bool result' => [
            static fn (): int => 0,
            StatusCheckerInterface::STATUS_FAIL,
        ];

        yield 'invokable class returning true' => [
            new class {
                public function __invoke(): bool
                {
                    return true;
                }
            },
            StatusCheckerInterface::STATUS_OK,
        ];

        yield 'invokable class returning false' => [
            new class {
                public function __invoke(): bool
                {
                    return false;
                }
            },
            StatusCheckerInterface::STATUS_FAIL,
        ];

        yield 'static method array' => [
            [InvokableFixture::class, 'staticTrue'],
            StatusCheckerInterface::STATUS_OK,
        ];

        yield 'instance method array' => [
            [new InvokableFixture(), 'instanceTrue'],
            StatusCheckerInterface::STATUS_OK,
        ];
    }

    /**
     * @dataProvider callableProvider
     */
    public function testCheckStatusAcceptsAnyCallableForm(callable $callback, string $expected): void
    {
        $checker = new CallableChecker('test_check', $callback);

        self::assertSame($expected, $checker->checkStatus());
    }

    public function testFailingCallbackReturnsFailStatus(): void
    {
        $checker = new CallableChecker('test_check', static function (): bool {
            throw new RuntimeException('dependency down');
        });

        self::assertSame(StatusCheckerInterface::STATUS_FAIL, $checker->checkStatus());
    }

    public function testGetNameReturnsConstructorName(): void
    {
        $checker = new CallableChecker('my_check', static fn (): bool => true);

        self::assertSame('my_check', $checker->getName());
    }

    public function testNeedsOutputIsTrue(): void
    {
        $checker = new CallableChecker('my_check', static fn (): bool => true);

        self::assertTrue($checker->needsOutput());
    }

    public function testCriticalDefaultsToTrueAndIsMutable(): void
    {
        $checker = new CallableChecker('my_check', static fn (): bool => true);

        self::assertTrue($checker->isCritical());

        $result = $checker->setCritical(false);

        self::assertFalse($checker->isCritical());
        self::assertSame($checker, $result, 'setCritical should be fluent');
    }
}
