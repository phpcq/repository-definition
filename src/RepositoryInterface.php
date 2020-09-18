<?php

declare(strict_types=1);

namespace Phpcq\RepositoryDefinition;

use Generator;
use Phpcq\RepositoryDefinition\Plugin\PluginInterface;
use Phpcq\RepositoryDefinition\Tool\ToolInterface;

interface RepositoryInterface
{
    public function hasTool(string $name): bool;

    public function addTool(ToolInterface $tool): void;

    public function getTool(string $name): ToolInterface;

    public function hasPlugin(string $name): bool;

    public function getPlugin(string $name): PluginInterface;

    public function addPlugin(PluginInterface $plugin): void;

    /**
     * Iterate over all tools.
     *
     * @return Generator|ToolInterface[]
     *
     * @psalm-return Generator<ToolInterface>
     */
    public function iterateTools(): Generator;

    /**
     * Iterate over all plugins.
     *
     * @return Generator|PluginInterface[]
     *
     * @psalm-return Generator<PluginInterface>
     */
    public function iteratePlugins(): Generator;
}
