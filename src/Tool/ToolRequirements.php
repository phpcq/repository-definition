<?php

declare(strict_types=1);

namespace Phpcq\RepositoryDefinition\Tool;

use Phpcq\RepositoryDefinition\VersionRequirementList;

class ToolRequirements
{
    /**
     * Platform requirements.
     *
     * @var VersionRequirementList
     */
    private $phpRequirements;

    /**
     * Required composer libraries.
     *
     * @var VersionRequirementList
     */
    private $composerRequirements;

    /**
     * Create a new instance.
     */
    public function __construct()
    {
        $this->phpRequirements = new VersionRequirementList();
        $this->composerRequirements = new VersionRequirementList();
    }

    /**
     * Retrieve phpRequirements.
     *
     * @return VersionRequirementList
     */
    public function getPhpRequirements()
    {
        return $this->phpRequirements;
    }

    /**
     * Retrieve composerRequirements.
     *
     * @return VersionRequirementList
     */
    public function getComposerRequirements()
    {
        return $this->composerRequirements;
    }
}
