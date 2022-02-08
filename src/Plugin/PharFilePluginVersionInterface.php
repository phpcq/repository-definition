<?php

declare(strict_types=1);

namespace Phpcq\RepositoryDefinition\Plugin;

interface PharFilePluginVersionInterface extends PluginVersionInterface
{
    /**
     * Gets the path to the plugin.php inside the phar file, e.g. plugin.php
     *
     * The file itself must instantiate an instance of the plugin bootstrap itself and has to return it.
     *
     * @return string
     */
    public function getPluginPath(): string;
}
