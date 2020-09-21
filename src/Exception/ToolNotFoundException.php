<?php

declare(strict_types=1);

namespace Phpcq\RepositoryDefinition\Exception;

use Throwable;

final class ToolNotFoundException extends RuntimeException
{
    /**
     * @var string
     */
    private $tool;

    public function __construct(string $tool, int $code = 0, Throwable $previous = null)
    {
        parent::__construct(sprintf('Tool "%s" not found', $tool), $code, $previous);

        $this->tool = $tool;
    }

    /**
     * Get tool name.
     *
     * @return string
     */
    public function getTool(): string
    {
        return $this->tool;
    }
}
