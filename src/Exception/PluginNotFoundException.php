<?php

declare(strict_types=1);

namespace Phpcq\RepositoryDefinition\Exception;

use Throwable;

final class PluginNotFoundException extends RuntimeException
{
    /**
     * @var string
     */
    private $plugin;

    public function __construct(string $plugin, int $code = 0, Throwable $previous = null)
    {
        parent::__construct(sprintf('plugin "%s" not found', $plugin), $code, $previous);

        $this->plugin = $plugin;
    }

    /**
     * Get plugin name.
     *
     * @return string
     */
    public function getPlugin(): string
    {
        return $this->plugin;
    }
}
