<?php

declare(strict_types=1);

namespace Phpcq\RepositoryDefinition;

use Phpcq\RepositoryDefinition\Exception\JsonFileNotFoundException;

/** @psalm-import-type TRepositoryCheckSum from \Phpcq\RepositoryDefinition\RepositoryLoader */
interface JsonFileLoaderInterface
{
    /**
     * Loads a json file and returns it data as array.
     *
     * @psalm-param TRepositoryCheckSum|null $checksum
     *
     * @throws JsonFileNotFoundException If file does not exists.
     */
    public function load(string $file, ?array $checksum = null): array;
}
