<?php

declare(strict_types=1);

namespace Phpcq\RepositoryDefinition;

use Phpcq\RepositoryDefinition\Plugin\PhpFilePluginVersion;
use Phpcq\RepositoryDefinition\Plugin\Plugin;
use Phpcq\RepositoryDefinition\Plugin\PluginHash;
use Phpcq\RepositoryDefinition\Plugin\PluginRequirements;
use Phpcq\RepositoryDefinition\Plugin\PluginVersionInterface;
use Phpcq\RepositoryDefinition\Tool\Tool;
use Phpcq\RepositoryDefinition\Tool\ToolHash;
use Phpcq\RepositoryDefinition\Tool\ToolRequirements;
use Phpcq\RepositoryDefinition\Tool\ToolVersion;
use RuntimeException;

use function array_values;

/**
 * @psalm-import-type TRepositoryContents from JsonFileLoaderInterface
 * @psalm-import-type TRepositoryTool from JsonFileLoaderInterface
 * @psalm-import-type TRepositoryToolVersion from JsonFileLoaderInterface
 * @psalm-import-type TRepositoryToolRequirements from JsonFileLoaderInterface
 * @psalm-import-type TRepositoryPlugin from JsonFileLoaderInterface
 * @psalm-import-type TRepositoryPluginVersion from JsonFileLoaderInterface
 * @psalm-import-type TRepositoryPluginRequirements from JsonFileLoaderInterface
 * @psalm-import-type TRepositoryCheckSum from JsonFileLoaderInterface
 * @psalm-import-type TRepositoryIncludeList from JsonFileLoaderInterface
 */
final class RepositoryLoader
{
    /** @psalm-var array<string, Tool> */
    private $tools = [];

    /** @psalm-var array<string, Plugin> */
    private $plugins = [];

    /** @var JsonFileLoaderInterface */
    private $fileLoader;

    /**
     * @psalm-param TRepositoryCheckSum|null $checksum
     *
     * @psalm-return array{tools: list<Tool>, plugins: list<Plugin>}|null
     *
     * @deprecated Use self::loadData instead
     */
    public static function load(
        string $fileName,
        ?array $checksum = null,
        ?JsonFileLoaderInterface $fileLoader = null
    ): ?array {
        return self::loadData($fileName, $checksum, $fileLoader);
    }

    /**
     * @psalm-param TRepositoryCheckSum|null $checksum
     *
     * @psalm-return array{tools: list<Tool>, plugins: list<Plugin>}|null
     */
    public static function loadData(
        string $fileName,
        ?array $checksum = null,
        ?JsonFileLoaderInterface $fileLoader = null
    ): ?array {
        $instance = new RepositoryLoader($fileLoader);
        $instance->readFile($fileName, $checksum);

        /** @psalm-var list<Tool> $tools */
        $tools = array_values($instance->tools);
        /** @psalm-var list<Plugin> $plugins */
        $plugins = array_values($instance->plugins);

        if (empty($tools) && empty($plugins)) {
            return null;
        }

        return [
            'tools'   => $tools,
            'plugins' => $plugins,
        ];
    }

    /** @psalm-param TRepositoryCheckSum|null $checksum */
    public static function loadRepository(
        string $fileName,
        ?array $checksum = null,
        JsonFileLoaderInterface $fileLoader = null
    ): Repository {
        $repository = new Repository();
        $data       = self::loadData($fileName, $checksum, $fileLoader);
        if ($data === null) {
            return $repository;
        }

        foreach ($data['plugins'] as $plugin) {
            $repository->addPlugin($plugin);
        }
        foreach ($data['tools'] as $tool) {
            $repository->addTool($tool);
        }

        return $repository;
    }

    private function __construct(?JsonFileLoaderInterface $fileLoader = null)
    {
        $this->fileLoader = $fileLoader ?: new FileGetContentsJsonFileLoader();
    }

    /** @psalm-param TRepositoryCheckSum|null $checksum */
    private function readFile(string $fileName, ?array $checksum = null): void
    {
        /** @psalm-var TRepositoryContents $contents */
        $contents = $this->fileLoader->load($fileName, $checksum);
        $baseDir  = dirname($fileName);
        if (isset($contents['tools'])) {
            $this->walkTools($contents['tools']);
        }
        if (isset($contents['plugins'])) {
            $this->walkPlugins($contents['plugins'], $baseDir);
        }
        if (isset($contents['includes'])) {
            $this->walkIncludeFiles($contents['includes'], $baseDir);
        }
    }

    /**
     * @psalm-param array<string, TRepositoryTool> $tools
     */
    private function walkTools(array $tools): void
    {
        foreach ($tools as $toolName => $versions) {
            $this->walkToolVersions($toolName, $versions);
        }
    }

    /**
     * @psalm-param list<TRepositoryToolVersion> $versions
     */
    private function walkToolVersions(string $toolName, array $versions): void
    {
        if (!isset($this->tools[$toolName])) {
            $this->tools[$toolName] = new Tool($toolName);
        }
        foreach ($versions as $toolVersion) {
            $this->tools[$toolName]->addVersion(new ToolVersion(
                $toolName,
                $toolVersion['version'],
                $toolVersion['url'],
                $this->loadToolRequirements($toolVersion['requirements']),
                $this->loadToolHash($toolVersion['checksum'] ?? null),
                $toolVersion['signature'] ?? null,
            ));
        }
    }

    /**
     * @psalm-param TRepositoryCheckSum|null $hash
     */
    private function loadToolHash(?array $hash): ?ToolHash
    {
        if (null === $hash) {
            return null;
        }

        return ToolHash::create($hash['type'], $hash['value']);
    }

    /**
     * @psalm-param array<string, TRepositoryPlugin> $plugins
     */
    private function walkPlugins(array $plugins, string $baseDir): void
    {
        foreach ($plugins as $pluginName => $versions) {
            $this->walkPluginVersions($pluginName, $versions, $baseDir);
        }
    }

    /**
     * @psalm-param list<TRepositoryPluginVersion> $versions
     */
    private function walkPluginVersions(string $pluginName, array $versions, string $baseDir): void
    {
        if (!isset($this->plugins[$pluginName])) {
            $this->plugins[$pluginName] = new Plugin($pluginName);
        }
        foreach ($versions as $pluginVersion) {
            $this->plugins[$pluginName]->addVersion(
                $this->loadPluginVersion($pluginVersion, $pluginName, $baseDir)
            );
        }
    }

    /**
     * @psalm-param TRepositoryIncludeList $includes
     */
    private function walkIncludeFiles(array $includes, string $baseDir): void
    {
        foreach ($includes as $include) {
            $this->readFile($baseDir . '/' . $include['url'], $include['checksum'] ?? null);
        }
    }

    /** @psalm-param TRepositoryPluginVersion $information */
    private function loadPluginVersion(array $information, string $name, string $baseDir): PluginVersionInterface
    {
        switch ($information['type']) {
            case 'php-file':
                assert(isset($information['url']), 'Code is mandatory for inline plugins');
                return new PhpFilePluginVersion(
                    $name,
                    $information['version'],
                    $information['api-version'],
                    $this->loadPluginRequirements($information['requirements'] ?? []),
                    $baseDir . '/' . $information['url'],
                    $information['signature'] ?? null,
                    $this->loadPluginHash($information['checksum'] ?? null)
                );
        }

        throw new RuntimeException('Unexpected plugin type encountered ' . $information['type']);
    }

    /**
     * @psalm-param TRepositoryCheckSum|null $hash
     */
    private function loadPluginHash(?array $hash): ?PluginHash
    {
        if (null === $hash) {
            return null;
        }

        return PluginHash::create($hash['type'], $hash['value']);
    }

    /** @psalm-param TRepositoryToolRequirements|null $requirements */
    private function loadToolRequirements(?array $requirements): ToolRequirements
    {
        $result = new ToolRequirements();
        if (empty($requirements)) {
            return $result;
        }

        foreach (
            [
                'php'      => $result->getPhpRequirements(),
                'composer' => $result->getComposerRequirements(),
            ] as $key => $list
        ) {
            foreach ($requirements[$key] ?? [] as $name => $version) {
                $list->add(new VersionRequirement($name, $version));
            }
        }

        return $result;
    }

    /** @psalm-param TRepositoryPluginRequirements|null $requirements */
    private function loadPluginRequirements(?array $requirements): PluginRequirements
    {
        $result = new PluginRequirements();
        if (empty($requirements)) {
            return $result;
        }

        foreach (
            [
                'php'      => $result->getPhpRequirements(),
                'tool'     => $result->getToolRequirements(),
                'plugin'   => $result->getPluginRequirements(),
                'composer' => $result->getComposerRequirements(),
            ] as $key => $list
        ) {
            foreach ($requirements[$key] ?? [] as $name => $version) {
                $list->add(new VersionRequirement($name, $version));
            }
        }

        return $result;
    }
}
