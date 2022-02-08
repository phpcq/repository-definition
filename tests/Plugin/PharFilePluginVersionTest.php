<?php

declare(strict_types=1);

namespace Phpcq\RepositoryDefinition\Test\Plugin;

use Phpcq\RepositoryDefinition\Plugin\PharFilePluginVersion;
use Phpcq\RepositoryDefinition\Plugin\PluginHash;
use Phpcq\RepositoryDefinition\Plugin\PluginRequirements;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * @covers \Phpcq\RepositoryDefinition\Plugin\AbstractPluginVersion
 * @covers \Phpcq\RepositoryDefinition\Plugin\PharFilePluginVersion
 */
class PharFilePluginVersionTest extends TestCase
{
    public function testGetters(): void
    {
        $instance = new PharFilePluginVersion(
            'plugin-a',
            '2.0.0',
            '1.0.0',
            $requirements = new PluginRequirements(),
            __FILE__,
            'plugin.php',
            null,
            PluginHash::create(PluginHash::SHA_512, 'hashy-corp')
        );
        self::assertSame('plugin-a', $instance->getName());
        self::assertSame('2.0.0', $instance->getVersion());
        self::assertSame('1.0.0', $instance->getApiVersion());
        self::assertSame($requirements, $instance->getRequirements());
        self::assertNull($instance->getSignaturePath());
        self::assertSame(__FILE__, $instance->getFilePath());
        self::assertSame('plugin.php', $instance->getPluginPath());
        self::assertInstanceOf(PluginHash::class, $instance->getHash());
    }

    public function testGetsSignatureFromFile(): void
    {
        $instance = new PharFilePluginVersion(
            'plugin-a',
            '2.0.0',
            '1.0.0',
            new PluginRequirements(),
            __FILE__,
            'plugin.php',
            __FILE__,
            PluginHash::create(PluginHash::SHA_512, 'hashy-corp')
        );

        self::assertSame(__FILE__, $instance->getSignaturePath());
    }

    public function testThrowsForInvalidVersion(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid version string: 11.0.0');
        new PharFilePluginVersion(
            'plugin-a',
            '2.0.0',
            '11.0.0',
            new PluginRequirements(),
            __FILE__,
            'plugin.php',
            null,
            PluginHash::create(PluginHash::SHA_512, 'hashy-corp')
        );
    }
}
