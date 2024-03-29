<?php

declare(strict_types=1);

namespace Phpcq\RepositoryDefinition;

use Phpcq\RepositoryDefinition\Exception\RuntimeException;
use Phpcq\RepositoryDefinition\Plugin\PhpFilePluginVersion;
use Phpcq\RepositoryDefinition\Plugin\Plugin;
use Phpcq\RepositoryDefinition\Plugin\PluginHash;
use Phpcq\RepositoryDefinition\Plugin\PluginRequirements;
use Phpcq\RepositoryDefinition\Plugin\PluginVersionInterface;
use Phpcq\RepositoryDefinition\Tool\Tool;
use Phpcq\RepositoryDefinition\Tool\ToolHash;
use Phpcq\RepositoryDefinition\Tool\ToolRequirements;
use Phpcq\RepositoryDefinition\Tool\ToolVersion;

use function array_map;
use function array_values;
use function explode;
use function filter_var;
use function implode;
use function is_file;
use function parse_url;
use function str_replace;

use const FILTER_VALIDATE_URL;
use const PHP_URL_PATH;

/**
 * @psalm-type TRepositoryCheckSum = array{
 *   type: string,
 *   value: string,
 * }
 * @psalm-type TRepositoryIncludeList = list<array{
 *   url: string,
 *   checksum: TRepositoryCheckSum
 * }>
 * @psalm-type TRepositoryToolRequirements = array{
 *   php?: array<string, string>,
 *   composer?: array<string, string>,
 * }
 * @psalm-type TRepositoryToolVersion = array{
 *   version: string,
 *   url: string,
 *   requirements: TRepositoryToolRequirements,
 *   checksum?: TRepositoryCheckSum,
 *   signature?: string,
 * }
 * @psalm-type TRepositoryPluginRequirements = array{
 *   php?: array<string, string>,
 *   tool?: array<string, string>,
 *   plugin?: array<string, string>,
 *   composer?: array<string, string>,
 * }
 * @psalm-type TRepositoryPluginVersion = array{
 *   type: 'php-file'|'php-inline',
 *   version: string,
 *   api-version: string,
 *   requirements?: TRepositoryPluginRequirements,
 *   url?: string,
 *   checksum: TRepositoryCheckSum,
 *   signature?: string,
 * }
 * @psalm-type TRepositoryInclude = array{
 *  url: string,
 *  checksum: TRepositoryCheckSum
 * }
 * @psalm-type TRepositoryTool = list<TRepositoryToolVersion>
 * @psalm-type TRepositoryPlugin = list<TRepositoryPluginVersion>
 * @psalm-type TRepositoryContents = array{
 *  includes?: list<TRepositoryInclude>,
 *  tools?: array<string, TRepositoryTool>,
 *  plugins?: array<string, TRepositoryPlugin>,
 * }
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

        $tools = array_values($instance->tools);
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
            $this->walkTools($contents['tools'], $baseDir);
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
    private function walkTools(array $tools, string $baseDir): void
    {
        foreach ($tools as $toolName => $versions) {
            $this->walkToolVersions($toolName, $versions, $baseDir);
        }
    }

    /**
     * @psalm-param list<TRepositoryToolVersion> $versions
     */
    private function walkToolVersions(string $toolName, array $versions, string $baseDir): void
    {
        if (!isset($this->tools[$toolName])) {
            $this->tools[$toolName] = new Tool($toolName);
        }
        foreach ($versions as $toolVersion) {
            $this->tools[$toolName]->addVersion(new ToolVersion(
                $toolName,
                $toolVersion['version'],
                $this->validateUrlOrFile($toolVersion['url'], $baseDir),
                $this->loadToolRequirements($toolVersion['requirements']),
                $this->loadToolHash($toolVersion['checksum'] ?? null),
                isset($toolVersion['signature'])
                    ? $this->validateUrlOrFile($toolVersion['signature'], $baseDir)
                    : null,
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
            $this->readFile($this->validateUrlOrFile($include['url'], $baseDir), $include['checksum'] ?? null);
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
                    $this->validateUrlOrFile($information['url'], $baseDir),
                    isset($information['signature'])
                        ? $this->validateUrlOrFile($information['signature'], $baseDir)
                        : null,
                    $this->loadPluginHash($information['checksum'])
                );
        }

        throw new RuntimeException('Unexpected plugin type encountered ' . $information['type']);
    }

    /**
     * @psalm-param TRepositoryCheckSum $hash
     */
    private function loadPluginHash(array $hash): PluginHash
    {
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

    private function validateUrlOrFile(string $url, string $baseDir): string
    {
        // Local absolute path?
        if (is_file($url)) {
            return $url;
        }
        // Local relative path?
        if ('' !== $baseDir && is_file($baseDir . '/' . $url)) {
            return $baseDir . '/' . $url;
        }
        // Perform URL check.
        $path        = parse_url($url, PHP_URL_PATH);
        $encodedPath = array_map('urlencode', explode('/', $path));
        $newUrl      = str_replace($path, implode('/', $encodedPath), $url);
        if (filter_var($newUrl, FILTER_VALIDATE_URL)) {
            return $newUrl;
        }
        $newUrl = $baseDir . '/' . $newUrl;
        if (filter_var($newUrl, FILTER_VALIDATE_URL)) {
            return $newUrl;
        }

        // Did not understand.
        throw new RuntimeException('Invalid URI passed: ' . $url);
    }
}
