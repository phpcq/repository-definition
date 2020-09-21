<?php

declare(strict_types=1);

namespace Phpcq\RepositoryDefinition;

use Generator;
use Phpcq\RepositoryDefinition\Exception\PluginNotFoundException;
use Phpcq\RepositoryDefinition\Exception\ToolNotFoundException;
use Phpcq\RepositoryDefinition\Plugin\PluginInterface;
use Phpcq\RepositoryDefinition\Tool\ToolInterface;

class Repository implements RepositoryInterface
{
    /** @var ToolInterface[] */
    private $tools = [];

    /** @var PluginInterface[] */
    private $plugins = [];

    public function hasTool(string $name): bool
    {
        return isset($this->tools[$name]);
    }

    public function addTool(ToolInterface $tool): void
    {
        if ($this->hasTool($tool->getName())) {
            throw new \InvalidArgumentException('Tool ' . $tool->getName() . ' already exists');
        }

        $this->tools[$tool->getName()] = $tool;
    }

    public function getTool(string $name): ToolInterface
    {
        if (!isset($this->tools[$name])) {
            throw new ToolNotFoundException($name);
        }

        return $this->tools[$name];
    }

    public function hasPlugin(string $name): bool
    {
        return isset($this->plugins[$name]);
    }

    public function addPlugin(PluginInterface $plugin): void
    {
        if ($this->hasPlugin($plugin->getName())) {
            throw new \InvalidArgumentException('Tool ' . $plugin->getName() . ' already exists');
        }

        $this->plugins[$plugin->getName()] = $plugin;
    }

    public function getPlugin(string $name): PluginInterface
    {
        if (!isset($this->plugins[$name])) {
            throw new PluginNotFoundException($name);
        }

        return $this->plugins[$name];
    }

    /**
     * Iterate over all tools.
     *
     * @return Generator|ToolInterface[]
     *
     * @psalm-return Generator<ToolInterface>
     */
    public function iterateTools(): Generator
    {
        foreach ($this->tools as $tool) {
            yield $tool;
        }
    }

    /**
     * Iterate over all plugins.
     *
     * @return Generator|PluginInterface[]
     *
     * @psalm-return Generator<PluginInterface>
     */
    public function iteratePlugins(): Generator
    {
        foreach ($this->plugins as $plugin) {
            yield $plugin;
        }
    }
}
