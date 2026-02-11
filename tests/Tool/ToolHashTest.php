<?php

declare(strict_types=1);

namespace Phpcq\RepositoryDefinition\Test\Tool;

use Phpcq\RepositoryDefinition\Exception\InvalidHashException;
use Phpcq\RepositoryDefinition\Tool\ToolHash;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Phpcq\RepositoryDefinition\Tool\ToolHash
 */
final class ToolHashTest extends TestCase
{
    public static function hashProvider(): array
    {
        return [
            'SHA_1' => [ToolHash::SHA_1, 'hash-value'],
            'SHA_256' => [ToolHash::SHA_256, 'hash-value'],
            'SHA_384' => [ToolHash::SHA_384, 'hash-value'],
            'SHA_512' => [ToolHash::SHA_512, 'hash-value'],
        ];
    }

    #[DataProvider('hashProvider')]
    public function testToolInitializesHash(string $hashType, string $hashValue): void
    {
        $hash = ToolHash::create($hashType, $hashValue);
        // Would throw if not created.
        $this->assertSame($hashType, $hash->getType());
        $this->assertSame($hashValue, $hash->getValue());
    }

    public function testThrowsForInvalidHashType(): void
    {
        $this->expectException(InvalidHashException::class);
        $this->expectExceptionMessage('Invalid hash type: unknown-type (hash-value)');

        ToolHash::create('unknown-type', 'hash-value');
    }

    public static function equalsProvider(): array
    {
        return [
            'equals with identical type and value' => [
                'expected'  => true,
                'leftType' => ToolHash::SHA_1,
                'leftValue' => 'content',
                'rightType' => ToolHash::SHA_1,
                'rightValue' => 'content'
            ],
            'does not equal with identical type but different value' => [
                'expected'  => false,
                'leftType' => ToolHash::SHA_1,
                'leftValue' => 'content',
                'rightType' => ToolHash::SHA_1,
                'rightValue' => 'bar'
            ],
            'does not equal with different type but identical value' => [
                'expected'  => false,
                'leftType' => ToolHash::SHA_1,
                'leftValue' => 'content',
                'rightType' => ToolHash::SHA_256,
                'rightValue' => 'bar'
            ],
        ];
    }

    #[DataProvider('equalsProvider')]
    public function testEquals(
        bool $expected,
        string $leftType,
        string $leftValue,
        string $rightType,
        string $rightValue
    ): void {
        $left = ToolHash::create($leftType, $leftValue);
        $right = ToolHash::create($rightType, $rightValue);

        $this->assertEquals($expected, $left->equals($right));
        $this->assertEquals($expected, $right->equals($left));
    }
}
