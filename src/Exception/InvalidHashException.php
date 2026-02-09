<?php

declare(strict_types=1);

namespace Phpcq\RepositoryDefinition\Exception;

use RuntimeException;

class InvalidHashException extends RuntimeException
{
    public function __construct(private readonly string $hashType, private readonly string $hashValue)
    {
        parent::__construct('Invalid hash type: ' . $this->hashType . ' (' . $this->hashValue . ')');
    }

    public function getHashType(): string
    {
        return $this->hashType;
    }

    public function getHashValue(): string
    {
        return $this->hashValue;
    }
}
