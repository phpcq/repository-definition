<?php

declare(strict_types=1);

namespace Phpcq\RepositoryDefinition\Test\Plugin;

use InvalidArgumentException;
use Phpcq\RepositoryDefinition\Plugin\ApiVersionRange;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/** @covers \Phpcq\RepositoryDefinition\Plugin\ApiVersionRange */
final class ApiVersionRangeTest extends TestCase
{
    public static function provideContains(): iterable
    {
        yield 'min boundary' => ['1.0.0', true];
        yield 'inside' => ['1.0.5', true];
        yield 'max boundary' => ['1.1.0', true];
        yield 'above max' => ['1.2.0', false];
        yield 'below min' => ['0.9.0', false];
        yield 'next major' => ['2.0.0', false];
        yield 'invalid format' => ['1.1', false];
    }

    #[DataProvider('provideContains')]
    public function testContains(string $version, bool $expected): void
    {
        $range = new ApiVersionRange('1.0.0', '1.1.0');

        self::assertSame($expected, $range->contains($version));
    }

    public function testGetters(): void
    {
        $range = new ApiVersionRange('1.0.0', '1.1.0');

        self::assertSame('1.0.0', $range->getMin());
        self::assertSame('1.1.0', $range->getMax());
    }

    public function testThrowsForInvalidBoundary(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ApiVersionRange('1.0', '1.1.0');
    }

    public function testThrowsForMinGreaterThanMax(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ApiVersionRange('1.2.0', '1.1.0');
    }
}
