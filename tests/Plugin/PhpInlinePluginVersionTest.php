<?php

declare(strict_types=1);

namespace Phpcq\RepositoryDefinition\Test\Plugin;

use Phpcq\RepositoryDefinition\Plugin\PhpInlinePluginVersion;
use Phpcq\RepositoryDefinition\Plugin\PluginHash;
use Phpcq\RepositoryDefinition\Plugin\PluginRequirements;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * @covers \Phpcq\RepositoryDefinition\Plugin\AbstractPluginVersion
 * @covers \Phpcq\RepositoryDefinition\Plugin\PhpInlinePluginVersion
 */
class PhpInlinePluginVersionTest extends TestCase
{
    public function testGetters(): void
    {
        $instance = new PhpInlinePluginVersion(
            'plugin-a',
            '2.0.0',
            '1.0.0',
            $requirements = new PluginRequirements(),
            'code',
        );
        $this->assertSame('plugin-a', $instance->getName());
        $this->assertSame('2.0.0', $instance->getVersion());
        $this->assertSame('1.0.0', $instance->getApiVersion());
        $this->assertSame($requirements, $instance->getRequirements());
        $this->assertSame('code', $instance->getCode());
        $this->assertNull($instance->getSignature());
        $this->assertInstanceOf(PluginHash::class, $instance->getHash());
    }

    public function testThrowsForInvalidVersion(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid version string: 11.0.0');
        new PhpInlinePluginVersion(
            'plugin-a',
            '2.0.0',
            '11.0.0',
            new PluginRequirements(),
            'code',
        );
    }
}
