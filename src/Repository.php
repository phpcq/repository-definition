<?php

declare(strict_types=1);

namespace Phpcq\RepositoryDefinition;

use Generator;
use Phpcq\RepositoryDefinition\Plugin\Plugin;
use Phpcq\RepositoryDefinition\Tool\Tool;

class Repository
{
    /** @var Tool[] */
    private $tools = [];

    /** @var Plugin[] */
    private $plugins = [];

    public function getTool(string $name): Tool
    {
        if (!isset($this->tools[$name])) {
            $this->tools[$name] = new Tool($name);
        }

        return $this->tools[$name];
    }

    public function getPlugin(string $name): Plugin
    {
        if (!isset($this->plugins[$name])) {
            $this->plugins[$name] = new Plugin($name);
        }

        return $this->plugins[$name];
    }

    /**
     * Iterate over all tools.
     *
     * @return Generator|Tool[]
     *
     * @psalm-return Generator<Tool>
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
     * @return Generator|Plugin[]
     *
     * @psalm-return Generator<Plugin>
     */
    public function iteratePlugins(): Generator
    {
        foreach ($this->plugins as $plugin) {
            yield $plugin;
        }
    }
}
