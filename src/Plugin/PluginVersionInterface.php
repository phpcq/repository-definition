<?php

declare(strict_types=1);

namespace Phpcq\RepositoryDefinition\Plugin;

/**
 * Describes a plugin.
 */
interface PluginVersionInterface
{
    public function getName(): string;

    public function getApiVersion(): string;

    public function getVersion(): string;

    public function getFilePath(): string;

    public function getSignaturePath(): ?string;

    public function getHash(): PluginHash;

    public function getRequirements(): PluginRequirements;

    public function merge(PluginVersionInterface $other): void;
}
