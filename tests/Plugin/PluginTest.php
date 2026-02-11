<?php

declare(strict_types=1);

namespace Phpcq\RepositoryDefinition\Test\Plugin;

use InvalidArgumentException;
use LogicException;
use Phpcq\RepositoryDefinition\Plugin\Plugin;
use Phpcq\RepositoryDefinition\Plugin\PluginHash;
use Phpcq\RepositoryDefinition\Plugin\PluginRequirements;
use Phpcq\RepositoryDefinition\Plugin\PluginVersionInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Phpcq\RepositoryDefinition\Plugin\Plugin
 */
class PluginTest extends TestCase
{
    public function testToolInitializesName(): void
    {
        $tool = new Plugin('supertool');
        $this->assertSame('supertool', $tool->getName());
        $this->assertSame([], iterator_to_array($tool->getIterator()));
    }

    public function testToolAddsVersion(): void
    {
        $tool = new Plugin('supertool');

        $tool->addVersion($version1 = $this->mockPluginVersion('supertool', '1.0.0', '1.0.0', null));
        $tool->addVersion($version2 = $this->mockPluginVersion('supertool', '2.0.0', '1.0.0', null));
        $this->assertSame([$version1, $version2], iterator_to_array($tool->getIterator()));
    }

    public function testToolThrowsWhenAddingAVersionForAnotherTool(): void
    {
        $tool = new Plugin('supertool');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Plugin name mismatch: anothertool');

        $tool->addVersion($this->mockPluginVersion('anothertool', '1.0.0', '1.0.0', null));
    }

    public function testToolThrowsWhenAddingAVersionTwice(): void
    {
        $tool = new Plugin('supertool');

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Version already added: 1.0.0');

        $tool->addVersion($this->mockPluginVersion('supertool', '1.0.0', '1.0.0', null));
        $tool->addVersion($this->mockPluginVersion('supertool', '1.0.0', '1.0.0', null));
    }

    public function testToolGetsVersion(): void
    {
        $tool = new Plugin('supertool');

        $tool->addVersion($version1 = $this->mockPluginVersion('supertool', '1.0.0', '1.0.0', null));
        $tool->addVersion($version2 = $this->mockPluginVersion('supertool', '2.0.0', '1.0.0', null));

        $this->assertSame($version1, $tool->getVersion('1.0.0'));
        $this->assertSame($version2, $tool->getVersion('2.0.0'));
    }

    public function testToolThrowsWhenFetchingAnUnknownVersion(): void
    {
        $tool = new Plugin('supertool');

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Version not added: 1.0.0');

        $tool->getVersion('1.0.0');
    }

    private function mockPluginVersion(
        string $name,
        string $version,
        string $apiVersion,
        ?PluginHash $hash
    ): PluginVersionInterface {
        $mock = $this->createMock(PluginVersionInterface::class);
        $mock->method('getName')->willReturn($name);
        $mock->method('getVersion')->willReturn($version);
        $mock->method('getApiVersion')->willReturn($apiVersion);
        $mock->method('getFilePath')->willReturn('/path/to/plugin');
        $mock->method('getSignaturePath')->willReturn(null);
        $mock->method('getHash')->willReturn($hash ?? PluginHash::create(PluginHash::SHA_512, 'hashy-corp'));
        $mock->method('getRequirements')->willReturn(new PluginRequirements());

        return $mock;
    }
}
