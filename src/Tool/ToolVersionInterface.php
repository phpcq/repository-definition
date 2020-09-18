<?php

declare(strict_types=1);

namespace Phpcq\RepositoryDefinition\Tool;

interface ToolVersionInterface
{
    public function getName(): string;

    public function getVersion(): string;

    public function getPharUrl(): ?string;

    public function getHash(): ?ToolHash;

    public function getSignatureUrl(): ?string;

    public function getRequirements(): ToolRequirements;

    public function merge(self $other): void;
}
