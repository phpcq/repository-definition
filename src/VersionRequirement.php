<?php

declare(strict_types=1);

namespace Phpcq\RepositoryDefinition;

class VersionRequirement
{
    /**
     * Create a new instance.
     *
     * @param string $name
     * @param string $constraint
     */
    public function __construct(private readonly string $name, private readonly string $constraint = '*')
    {
    }

    /**
     * Retrieve name.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Retrieve constraint.
     *
     * @return string
     */
    public function getConstraint(): string
    {
        return $this->constraint;
    }
}
