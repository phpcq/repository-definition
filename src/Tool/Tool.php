<?php

declare(strict_types=1);

namespace Phpcq\RepositoryDefinition\Tool;

use InvalidArgumentException;
use IteratorAggregate;
use LogicException;
use Traversable;

/**
 * This holds all versions of a tool.
 *
 * @template-implements IteratorAggregate<int, ToolVersionInterface>
 */
class Tool implements IteratorAggregate, ToolInterface
{
    /**
     * The name of the tool.
     *
     * @var string
     */
    private $name;

    /**
     * All versions of the tool.
     *
     * @var ToolVersionInterface[]
     */
    private $versions = [];

    /**
     * Create a new instance.
     *
     * @param string $name The name of the tool.
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * Retrieve name.
     */
    public function getName(): string
    {
        return $this->name;
    }

    public function addVersion(ToolVersionInterface $version): void
    {
        if ($version->getName() !== $this->name) {
            throw new InvalidArgumentException('Tool name mismatch: ' . $version->getName());
        }
        if ($this->has($version->getVersion())) {
            throw new LogicException('Version already added: ' . $version->getVersion());
        }

        $this->versions[$version->getVersion()] = $version;
    }

    public function getVersion(string $version): ToolVersionInterface
    {
        if (!$this->has($version)) {
            throw new LogicException('Version not added: ' . $version);
        }
        return $this->versions[$version];
    }

    public function has(string $version): bool
    {
        return isset($this->versions[$version]);
    }

    public function isEmpty(): bool
    {
        return empty($this->versions);
    }

    /**
     * Iterate over all versions.
     *
     * @return Traversable<int, ToolVersionInterface>
     */
    public function getIterator(): Traversable
    {
        foreach ($this->versions as $version) {
            yield $version;
        }
    }
}
