<?php

declare(strict_types=1);

namespace Phpcq\RepositoryDefinition;

use Phpcq\RepositoryDefinition\Exception\JsonFileNotFoundException;

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
interface JsonFileLoaderInterface
{
    /**
     * Loads a json file and returns it data as array.
     *
     * @psalm-param TRepositoryCheckSum|null $checksum
     *
     * @throws JsonFileNotFoundException If file does not exists.
     *
     * @psalm-return TRepositoryContents
     */
    public function load(string $file, ?array $checksum = null): array;
}
