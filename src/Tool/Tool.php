<?php

declare(strict_types=1);

namespace Phpcq\RepositoryDefinition\Tool;

use InvalidArgumentException;
use IteratorAggregate;
use LogicException;
use Override;
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
     */
    private string $name;

    /**
     * All versions of the tool.
     *
     * @var array<string, ToolVersionInterface>
     */
    private array $versions = [];

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
    #[Override]
    public function getName(): string
    {
        return $this->name;
    }

    #[Override]
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

    #[Override]
    public function getVersion(string $version): ToolVersionInterface
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
     * @return Traversable<int, ToolVersionInterface>
     */
    #[Override]
    public function getIterator(): Traversable
    {
        foreach ($this->versions as $version) {
            yield $version;
        }
    }
}
