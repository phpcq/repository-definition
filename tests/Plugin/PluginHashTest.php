<?php

declare(strict_types=1);

namespace Phpcq\RepositoryDefinition\Test\Plugin;

use Phpcq\RepositoryDefinition\Exception\InvalidHashException;
use Phpcq\RepositoryDefinition\Plugin\PluginHash;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Phpcq\RepositoryDefinition\Plugin\PluginHash
 */
final class PluginHashTest extends TestCase
{
    public static function hashProvider(): array
    {
        return [
            'SHA_1' => [PluginHash::SHA_1, 'hash-value'],
            'SHA_256' => [PluginHash::SHA_256, 'hash-value'],
            'SHA_384' => [PluginHash::SHA_384, 'hash-value'],
            'SHA_512' => [PluginHash::SHA_512, 'hash-value'],
        ];
    }

    #[DataProvider('hashProvider')]
    public function testToolInitializesHash(string $hashType, string $hashValue): void
    {
        $hash = PluginHash::create($hashType, $hashValue);
        // Would throw if not created.
        $this->assertSame($hashType, $hash->getType());
        $this->assertSame($hashValue, $hash->getValue());
    }

    public function testThrowsForInvalidHashType(): void
    {
        $this->expectException(InvalidHashException::class);
        $this->expectExceptionMessage('Invalid hash type: unknown-type (hash-value)');

        PluginHash::create('unknown-type', 'hash-value');
    }

    public static function equalsProvider(): array
    {
        return [
            'equals with identical type and value' => [
                'expected'  => true,
                'leftType' => PluginHash::SHA_1,
                'leftValue' => 'content',
                'rightType' => PluginHash::SHA_1,
                'rightValue' => 'content'
            ],
            'does not equal with identical type but different value' => [
                'expected'  => false,
                'leftType' => PluginHash::SHA_1,
                'leftValue' => 'content',
                'rightType' => PluginHash::SHA_1,
                'rightValue' => 'bar'
            ],
            'does not equal with different type but identical value' => [
                'expected'  => false,
                'leftType' => PluginHash::SHA_1,
                'leftValue' => 'content',
                'rightType' => PluginHash::SHA_256,
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
        $left = PluginHash::create($leftType, $leftValue);
        $right = PluginHash::create($rightType, $rightValue);

        $this->assertEquals($expected, $left->equals($right));
        $this->assertEquals($expected, $right->equals($left));
    }
}
