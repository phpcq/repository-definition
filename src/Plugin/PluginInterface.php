<?php

declare(strict_types=1);

namespace Phpcq\RepositoryDefinition\Plugin;

use Traversable;

/**
 * @extends Traversable<PluginVersionInterface>
 */
interface PluginInterface extends Traversable
{
    /**
     * Retrieve name.
     */
    public function getName(): string;

    public function addVersion(PluginVersionInterface $version): void;

    public function getVersion(string $version): PluginVersionInterface;

    public function has(string $version): bool;

    public function isEmpty(): bool;
}
