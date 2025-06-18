<?php

declare(strict_types=1);

namespace Phpcq\RepositoryDefinition\Plugin;

use InvalidArgumentException;
use IteratorAggregate;
use LogicException;
use Override;
use Traversable;

/**
 * This holds all versions of a plugin.
 *
 * @template-implements IteratorAggregate<int, PluginVersionInterface>
 */
class Plugin implements IteratorAggregate, PluginInterface
{
    /**
     * The name of the plugin.
     *
     * @var string
     */
    private $name;

    /**
     * All versions of the plugin.
     *
     * @var PluginVersionInterface[]
     */
    private $versions = [];

    /**
     * Create a new instance.
     *
     * @param string $name The name of the plugin.
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * Retrieve name.
     */
    #[Override]
    public function getName(): string
    {
        return $this->name;
    }

    #[Override]
    public function addVersion(PluginVersionInterface $version): void
    {
        if ($version->getName() !== $this->name) {
            throw new InvalidArgumentException('Plugin name mismatch: ' . $version->getName());
        }
        if ($this->has($version->getVersion())) {
            throw new LogicException('Version already added: ' . $version->getVersion());
        }

        $this->versions[$version->getVersion()] = $version;
    }

    #[Override]
    public function getVersion(string $version): PluginVersionInterface
    {
        if (!$this->has($version)) {
            throw new LogicException('Version not added: ' . $version);
        }
        return $this->versions[$version];
    }

    #[Override]
    public function has(string $version): bool
    {
        return isset($this->versions[$version]);
    }

    #[Override]
    public function isEmpty(): bool
    {
        return empty($this->versions);
    }

    /**
     * Iterate over all versions.
     *
     * @return Traversable<int, PluginVersionInterface>
     */
    #[Override]
    public function getIterator(): Traversable
    {
        foreach ($this->versions as $version) {
            yield $version;
        }
    }
}
