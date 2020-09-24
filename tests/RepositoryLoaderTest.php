<?php

declare(strict_types=1);

namespace Phpcq\RepositoryDefinition\Test;

use Generator;
use Phpcq\RepositoryDefinition\JsonFileLoaderInterface;
use Phpcq\RepositoryDefinition\RepositoryInterface;
use Phpcq\RepositoryDefinition\RepositoryLoader;
use PHPUnit\Framework\TestCase;

use function file_get_contents;
use function json_decode;

/** @covers \Phpcq\RepositoryDefinition\RepositoryLoader */
final class RepositoryLoaderTest extends TestCase
{
    public function providePlugins(): Generator
    {
        yield [
            'relative',
            '1.0.0',
            __DIR__ . '/fixtures/relative-plugin.php',
            __DIR__ . '/fixtures/relative-plugin.php.asc',
        ];
        yield [
            'url',
            '1.0.0',
            'https://example.org/plugin-1.0.0.php',
            'https://example.org/plugin-1.0.0.php.asc',
        ];
        yield [
            'include',
            '1.0.0',
            __DIR__ . '/fixtures/./includes/include-plugin-1.0.0.php',
            __DIR__ . '/fixtures/./includes/include-plugin-1.0.0.php.asc',
        ];
    }

    /** @dataProvider providePlugins */
    public function testUrlAndPathResolvingForPlugins(
        string $plugin,
        string $version,
        string $file,
        string $signature
    ): void {
        $repository = $this->loadRepository();

        $this->assertSame($file, $repository->getPlugin($plugin)->getVersion($version)->getFilePath());
        $this->assertSame($signature, $repository->getPlugin($plugin)->getVersion($version)->getSignaturePath());
    }

    public function provideTools(): Generator
    {
        yield [
            'relative',
            '1.0.0',
            __DIR__ . '/fixtures/relative-tool.phar',
            __DIR__ . '/fixtures/relative-tool.phar.asc',
        ];
        yield [
            'url',
            '1.0.0',
            'https://example.org/url.phar',
            'https://example.org/url.phar.asc',
        ];
        yield [
            'include-relative',
            '1.0.0',
            __DIR__ . '/fixtures/./includes/include-tool-1.0.0.php',
            __DIR__ . '/fixtures/./includes/include-tool-1.0.0.php.asc',
        ];
    }

    /** @dataProvider provideTools */
    public function testUrlAndPathResolvingForTools(
        string $tool,
        string $version,
        string $file,
        string $signature
    ): void {
        $repository = $this->loadRepository();

        $this->assertSame($file, $repository->getTool($tool)->getVersion($version)->getPharUrl());
        $this->assertSame($signature, $repository->getTool($tool)->getVersion($version)->getSignatureUrl());
    }

    private function loadRepository(): RepositoryInterface
    {
        $loader = $this->getMockForAbstractClass(JsonFileLoaderInterface::class);
        $loader->method('load')->willReturnCallback(
            static function (string $url) {
                switch ($url) {
                    case __DIR__ . '/fixtures/repository.json':
                    case __DIR__ . '/fixtures/./includes/include.json':
                        return json_decode(file_get_contents($url), true);

                    case 'http://example.org/repositories/include.json':
                        return [];
                }

                return [];
            }
        );

        return RepositoryLoader::loadRepository(__DIR__ . '/fixtures/repository.json', null, $loader);
    }
}
