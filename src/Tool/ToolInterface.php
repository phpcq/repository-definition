<?php

declare(strict_types=1);

namespace Phpcq\RepositoryDefinition\Tool;

use Traversable;

/**
 * Interface ToolInterface
 *
 * @extends Traversable<ToolVersionInterface>
 */
interface ToolInterface extends Traversable
{
    /**
     * Retrieve name.
     */
    public function getName(): string;

    public function addVersion(ToolVersionInterface $version): void;

    public function getVersion(string $version): ToolVersionInterface;

    public function has(string $version): bool;

    public function isEmpty(): bool;
}
