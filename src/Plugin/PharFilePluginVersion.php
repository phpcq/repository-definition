<?php

declare(strict_types=1);

namespace Phpcq\RepositoryDefinition\Plugin;

class PharFilePluginVersion extends AbstractPluginVersion implements PharFilePluginVersionInterface
{
    /**
     * @var string
     */
    private $pluginPath;

    public function __construct(
        string $name,
        string $version,
        string $apiVersion,
        ?PluginRequirements $requirements,
        string $filePath,
        string $pluginPath,
        ?string $signaturePath = null,
        PluginHash $hash
    ) {
        parent::__construct($name, $version, $apiVersion, $requirements, $filePath, $signaturePath, $hash);
        $this->pluginPath = $pluginPath;
    }

    public function getPluginPath(): string
    {
        return $this->pluginPath;
    }
}
