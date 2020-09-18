<?php

declare(strict_types=1);

namespace Phpcq\RepositoryDefinition\Test;

use Phpcq\RepositoryDefinition\VersionRequirement;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Phpcq\RepositoryDefinition\VersionRequirement
 */
class VersionRequirementTest extends TestCase
{
    public function testVersionRequirementInitializes(): void
    {
        $tool = new VersionRequirement('ext-foo', '*');
        $this->assertSame('ext-foo', $tool->getName());
        $this->assertSame('*', $tool->getConstraint());
    }
}
