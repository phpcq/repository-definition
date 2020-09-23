<?php

declare(strict_types=1);

namespace Phpcq\RepositoryDefinition\Test\Plugin;

use Phpcq\RepositoryDefinition\Exception\InvalidHashException;
use Phpcq\RepositoryDefinition\Plugin\PluginHash;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Phpcq\RepositoryDefinition\Plugin\PluginHash
 */
class PluginHashTest extends TestCase
{
    public function hashProvider(): array
    {
        return [
            'SHA_1' => [PluginHash::SHA_1, 'hash-value'],
            'SHA_256' => [PluginHash::SHA_256, 'hash-value'],
            'SHA_384' => [PluginHash::SHA_384, 'hash-value'],
            'SHA_512' => [PluginHash::SHA_512, 'hash-value'],
        ];
    }

    /**
     * @dataProvider hashProvider
     */
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

    public function equalsProvider(): array
    {
        return [
            'equals with identical type and value' => [
                'expected'  => true,
                'left_type' => PluginHash::SHA_1,
                'left_value' => 'content',
                'right_type' => PluginHash::SHA_1,
                'right_value' => 'content'
            ],
            'does not equal with identical type but different value' => [
                'expected'  => false,
                'left_type' => PluginHash::SHA_1,
                'left_value' => 'content',
                'right_type' => PluginHash::SHA_1,
                'right_value' => 'bar'
            ],
            'does not equal with different type but identical value' => [
                'expected'  => false,
                'left_type' => PluginHash::SHA_1,
                'left_value' => 'content',
                'right_type' => PluginHash::SHA_256,
                'right_value' => 'bar'
            ],
        ];
    }

    /**
     * @dataProvider equalsProvider
     */
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
