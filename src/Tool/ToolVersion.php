<?php

declare(strict_types=1);

namespace Phpcq\RepositoryDefinition\Tool;

use Phpcq\RepositoryDefinition\VersionRequirement;
use Phpcq\RepositoryDefinition\VersionRequirementList;

class ToolVersion implements ToolVersionInterface
{
    /** @var string */
    private $name;

    /** @var string */
    private $version;

    /** @var ?string */
    private $pharUrl;

    /** @var ?string */
    private $signatureUrl;

    /** @var ToolHash|null */
    private $hash;

    /** @var ToolRequirements */
    private $requirements;

    public function __construct(
        string $name,
        string $version,
        ?string $pharUrl,
        ?ToolRequirements $requirements,
        ?ToolHash $hash,
        ?string $signatureUrl
    ) {
        $this->name         = $name;
        $this->version      = $version;
        $this->pharUrl      = $pharUrl;
        $this->hash         = $hash;
        $this->signatureUrl = $signatureUrl;
        $this->requirements = $requirements ?? new ToolRequirements();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function getPharUrl(): ?string
    {
        return $this->pharUrl;
    }

    public function getHash(): ?ToolHash
    {
        return $this->hash;
    }

    public function getSignatureUrl(): ?string
    {
        return $this->signatureUrl;
    }

    public function getRequirements(): ToolRequirements
    {
        return $this->requirements;
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function merge(ToolVersionInterface $other): void
    {
        if (null !== ($data = $other->getPharUrl()) && $data !== $this->pharUrl) {
            $this->pharUrl = $data;
        }
        if (null !== ($data = $other->getSignatureUrl()) && $data !== $this->signatureUrl) {
            $this->signatureUrl = $data;
        }
        if (null !== ($data = $other->getHash()) && $data !== $this->hash) {
            if (
                null === $this->hash
                || $data->getType() !== $this->hash->getType()
                || $data->getValue() !== $this->hash->getValue()
            ) {
                $this->hash = ToolHash::create($data->getType(), $data->getValue());
            }
        }

        $otherRequirements = $other->getRequirements();
        foreach (
            [
                [$this->requirements->getPhpRequirements(), $otherRequirements->getPhpRequirements()],
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
