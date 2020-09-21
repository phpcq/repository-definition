<?php

declare(strict_types=1);

namespace Phpcq\RepositoryDefinition;

use Phpcq\RepositoryDefinition\Exception\JsonFileNotFoundException;

use function file_get_contents;
use function json_decode;

use const JSON_THROW_ON_ERROR;

/**
 * @psalm-import-type TRepositoryContents from JsonFileLoaderInterface
 */
final class FileGetContentsJsonFileLoader implements JsonFileLoaderInterface
{
    public function load(string $file): array
    {
        $data = file_get_contents($file);
        if (false === $data) {
            throw new JsonFileNotFoundException('Failed to load file: ' . $file);
        }

        /** @psalm-var TRepositoryContents */
        $data = json_decode($data, true, JSON_THROW_ON_ERROR);

        return $data;
    }
}