<?php

declare(strict_types=1);

namespace Setono\Composer\FileChanges\Configuration;

final class Configuration
{
    /**
     * @var list<string>
     *
     * Each path is relative to the composer.json directory
     */
    public array $paths = [];

    /**
     * @param string $relativeTo The path where the files are resolved relative to
     *
     * @return list<string>
     *
     * @throws \RuntimeException if a file doesn't exist
     */
    public function resolveFiles(string $relativeTo): array
    {
        $relativeTo = rtrim($relativeTo, '/');

        $files = [];
        foreach ($this->paths as $path) {
            $absolutePath = $relativeTo . '/' . $path;
            if (!file_exists($absolutePath)) {
                throw new \RuntimeException(sprintf('The path %s does not exist', $absolutePath));
            }

            if (is_file($absolutePath)) {
                $files[] = $absolutePath;

                continue;
            }

            $directoryIterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($absolutePath), \RecursiveIteratorIterator::LEAVES_ONLY);

            /** @var \SplFileInfo $file */
            foreach ($directoryIterator as $file) {
                if ($file->isDir()) {
                    continue;
                }

                $files[] = $file->getPathname();
            }
        }

        return $files;
    }
}
