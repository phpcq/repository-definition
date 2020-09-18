<?php

declare(strict_types=1);

namespace Phpcq\RepositoryDefinition\Repository\Plugin;

/**
 * Plugin with url.
 */
interface PhpFilePluginVersionInterface extends PluginVersionInterface
{
    public function getFilePath(): string;

    public function getSignaturePath(): ?string;
}
