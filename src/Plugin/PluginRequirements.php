<?php

declare(strict_types=1);

namespace Phpcq\RepositoryDefinition\Plugin;

use Phpcq\RepositoryDefinition\VersionRequirementList;

class PluginRequirements
{
    /**
     * Platform requirements.
     */
    private VersionRequirementList $phpRequirements;

    /**
     * Required tools.
     */
    private VersionRequirementList $toolRequirements;

    /**
     * Required peer plugins.
     */
    private VersionRequirementList $pluginRequirements;

    /**
     * Required composer libraries.
     */
    private VersionRequirementList $composerRequirements;

    /**
     * Create a new instance.
     */
    public function __construct()
    {
        $this->phpRequirements = new VersionRequirementList();
        $this->toolRequirements = new VersionRequirementList();
        $this->pluginRequirements = new VersionRequirementList();
        $this->composerRequirements = new VersionRequirementList();
    }

    /**
     * Retrieve phpRequirements.
     */
    public function getPhpRequirements(): VersionRequirementList
    {
        return $this->phpRequirements;
    }

    /**
     * Retrieve toolRequirements.
     */
    public function getToolRequirements(): VersionRequirementList
    {
        return $this->toolRequirements;
    }

    /**
     * Retrieve pluginRequirements.
     */
    public function getPluginRequirements(): VersionRequirementList
    {
        return $this->pluginRequirements;
    }

    /**
     * Retrieve composerRequirements.
     */
    public function getComposerRequirements(): VersionRequirementList
    {
        return $this->composerRequirements;
    }

    public function __clone()
    {
        $this->composerRequirements = clone $this->composerRequirements;
        $this->phpRequirements      = clone $this->phpRequirements;
        $this->toolRequirements     = clone $this->toolRequirements;
        $this->pluginRequirements   = clone $this->pluginRequirements;
    }
}
