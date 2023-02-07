<?php

declare(strict_types=1);

namespace Setono\Composer\FileChanges\Configuration;

use Setono\Composer\FileChanges\ComposerData;

/**
 * Will extract the configuration from the composer.json extra key
 */
final class ComposerExtraConfigurationResolver implements ConfigurationResolverInterface
{
    public function __construct(private readonly ComposerData $composerData)
    {
    }

    // todo we should probably throw some exceptions in this method instead of silently accepting things
    public function resolve(): Configuration
    {
        $configuration = new Configuration();

        $extra = $this->composerData->getExtra();

        if (!array_key_exists('file-changes', $extra)) {
            return $configuration;
        }

        if (!is_array($extra['file-changes'])) {
            return $configuration;
        }

        if (!array_key_exists('paths', $extra['file-changes'])) {
            return $configuration;
        }

        $paths = $extra['file-changes']['paths'];
        if (!is_array($paths)) {
            return $configuration;
        }

        foreach ($paths as $path) {
            if (!is_string($path)) {
                continue;
            }

            $configuration->paths[] = $path;
        }

        return $configuration;
    }
}
