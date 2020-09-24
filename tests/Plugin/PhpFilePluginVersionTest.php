<?php

declare(strict_types=1);

namespace Phpcq\RepositoryDefinition\Test\Plugin;

use Phpcq\RepositoryDefinition\Plugin\PhpFilePluginVersion;
use Phpcq\RepositoryDefinition\Plugin\PluginHash;
use Phpcq\RepositoryDefinition\Plugin\PluginRequirements;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * @covers \Phpcq\RepositoryDefinition\Plugin\AbstractPluginVersion
 * @covers \Phpcq\RepositoryDefinition\Plugin\PhpFilePluginVersion
 */
class PhpFilePluginVersionTest extends TestCase
{
    public function testGetters(): void
    {
        $instance = new PhpFilePluginVersion(
            'plugin-a',
            '2.0.0',
            '1.0.0',
            $requirements = new PluginRequirements(),
            __FILE__,
            null,
            PluginHash::create(PluginHash::SHA_512, 'hashy-corp')
        );
        $this->assertSame('plugin-a', $instance->getName());
        $this->assertSame('2.0.0', $instance->getVersion());
        $this->assertSame('1.0.0', $instance->getApiVersion());
        $this->assertSame($requirements, $instance->getRequirements());
        $this->assertNull($instance->getSignaturePath());
        $this->assertSame(__FILE__, $instance->getFilePath());
        $this->assertInstanceOf(PluginHash::class, $instance->getHash());
    }

    public function testGetsSignatureFromFile(): void
    {
        $instance = new PhpFilePluginVersion(
            'plugin-a',
            '2.0.0',
            '1.0.0',
            new PluginRequirements(),
            __FILE__,
            __FILE__,
            PluginHash::create(PluginHash::SHA_512, 'hashy-corp')
        );

        $this->assertSame(__FILE__, $instance->getSignaturePath());
    }

    public function testThrowsForInvalidVersion(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid version string: 11.0.0');
        new PhpFilePluginVersion(
            'plugin-a',
            '2.0.0',
            '11.0.0',
            new PluginRequirements(),
            __FILE__,
            null,
            PluginHash::create(PluginHash::SHA_512, 'hashy-corp')
        );
    }
}
