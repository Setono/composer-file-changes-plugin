<?php

declare(strict_types=1);

namespace Setono\Composer\FileChanges\Configuration;

final class File
{
    public function __construct(
        public readonly string $filename,
        /**
         * The hash for the file
         */
        public readonly string $hash,
        /**
         * This is the configuration entry (inside composer.json) that produced this file.
         * You could call it the files configurational parent
         */
        public readonly string $configurationEntry,
    ) {
    }

    public function getLockData(): array
    {
        return [
            'hash' => $this->hash,
            'configurationEntry' => $this->configurationEntry,
        ];
    }
}
