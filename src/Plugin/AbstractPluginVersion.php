<?php

declare(strict_types=1);

namespace Phpcq\RepositoryDefinition\Plugin;

use Override;
use Phpcq\RepositoryDefinition\VersionRequirement;
use Phpcq\RepositoryDefinition\VersionRequirementList;
use RuntimeException;

abstract class AbstractPluginVersion implements PluginVersionInterface
{
    /** @var string */
    private $name;

    /** @var string */
    private $version;

    /** @var string */
    private $apiVersion;

    /** @var PluginHash */
    private $hash;

    /** @var PluginRequirements */
    private $requirements;

    /** @var string */
    private $filePath;

    /** @var string|null */
    private $signaturePath;

    public function __construct(
        string $name,
        string $version,
        string $apiVersion,
        ?PluginRequirements $requirements,
        string $filePath,
        ?string $signaturePath,
        PluginHash $hash
    ) {
        if ($apiVersion !== '1.0.0') {
            throw new RuntimeException('Invalid version string: ' . $apiVersion);
        }
        $this->name          = $name;
        $this->version       = $version;
        $this->apiVersion    = $apiVersion;
        $this->hash          = $hash;
        $this->filePath      = $filePath;
        $this->signaturePath = $signaturePath;
        $this->requirements  = $requirements ?? new PluginRequirements();
    }

    #[Override]
    public function getApiVersion(): string
    {
        return '1.0.0';
    }

    #[Override]
    public function getName(): string
    {
        return $this->name;
    }

    #[Override]
    public function getVersion(): string
    {
        return $this->version;
    }

    #[Override]
    public function getHash(): PluginHash
    {
        return $this->hash;
    }

    #[Override]
    public function getRequirements(): PluginRequirements
    {
        return $this->requirements;
    }

    #[Override]
    public function getFilePath(): string
    {
        return $this->filePath;
    }

    #[Override]
    public function getSignaturePath(): ?string
    {
        return $this->signaturePath;
    }

    #[Override]
    public function merge(PluginVersionInterface $other): void
    {
        $otherRequirements = $other->getRequirements();
        foreach (
            [
                [$this->requirements->getPhpRequirements(), $otherRequirements->getPhpRequirements()],
                [$this->requirements->getToolRequirements(), $otherRequirements->getToolRequirements()],
                [$this->requirements->getPluginRequirements(), $otherRequirements->getPluginRequirements()],
                [$this->requirements->getComposerRequirements(), $otherRequirements->getComposerRequirements()],
            ] as $lists
        ) {
            /** @var VersionRequirementList[] $lists */
            $target = $lists[0];
            $source = $lists[1];
            foreach ($source as $requirement) {
                if (!$target->has($requirement->getName())) {
                    $target->add(new VersionRequirement(
                        $requirement->getName(),
                        $requirement->getConstraint()
                    ));
                }
            }
        }
    }
}
