<?php

declare(strict_types=1);

namespace Phpcq\RepositoryDefinition\Test;

use LogicException;
use Phpcq\RepositoryDefinition\VersionRequirement;
use Phpcq\RepositoryDefinition\VersionRequirementList;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Phpcq\RepositoryDefinition\VersionRequirementList
 */
class VersionRequirementListTest extends TestCase
{
    public function testVersionRequirementListCanBeCreated(): void
    {
        $requirements = new VersionRequirementList();

        $this->assertSame([], iterator_to_array($requirements->getIterator()));
        $this->assertFalse($requirements->has('test2'));
    }

    public function testVersionRequirementListInitializesWithPassedRequirements(): void
    {
        $requirement1 = new VersionRequirement('test1');
        $requirement2 = new VersionRequirement('test2');
        $requirements = new VersionRequirementList([$requirement1, $requirement2]);

        $this->assertSame([$requirement1, $requirement2], iterator_to_array($requirements->getIterator()));
        $this->assertTrue($requirements->has('test1'));
        $this->assertTrue($requirements->has('test2'));
    }

    public function testVersionRequirementListThrowsForDuplicateRequirement(): void
    {
        $requirements = new VersionRequirementList([new VersionRequirement('test', '^1.0.0')]);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Requirement already added for test');

        $requirements->add(new VersionRequirement('test'));
    }

    public function testVersionRequirementListDoesNotThrowForDuplicateSameRequirement(): void
    {
        $requirements = new VersionRequirementList([new VersionRequirement('test', '1.0.0')]);

        $requirements->add(new VersionRequirement('test', '1.0.0'));
        // If we end up here, it was successful.
        $this->assertTrue($requirements->has('test'));
    }

    public function testVersionRequirementListCanRetrieveRequirement(): void
    {
        $requirements = new VersionRequirementList([$requirement = new VersionRequirement('test')]);

        $this->assertSame($requirement, $requirements->get('test'));
    }

    public function testVersionRequirementListThrowsWhenRetrievingUnknownRequirement(): void
    {
        $requirements = new VersionRequirementList();

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Requirement not added for test');

        $requirements->get('test');
    }

    public function testCountRequirements(): void
    {
        $requirement1 = new VersionRequirement('test1');
        $requirement2 = new VersionRequirement('test2');
        $requirements = new VersionRequirementList([$requirement1, $requirement2]);

        self::assertCount(2, $requirements);
    }
}
