<?php

declare(strict_types=1);

namespace Setono\Composer\FileChanges\Configuration;

use Webmozart\Glob\Glob;

final class Configuration
{
    /**
     * @var list<string>
     *
     * Each path is relative to the composer.json directory
     */
    private array $paths = [];

    /**
     * This method will return all files that the configured paths resolves to
     *
     * @param string $relativeTo The path where the files are resolved relative to
     *
     * @return list<File>
     *
     * @throws \RuntimeException if a file doesn't exist
     */
    public function resolveFiles(string $relativeTo): array
    {
        $relativeTo = rtrim(realpath($relativeTo), '/');

        $files = [];
        foreach ($this->paths as $path) {
            $absolutePath = $relativeTo . '/' . $path;

            if (is_file($absolutePath)) {
                $files[] = new File($absolutePath, md5_file($absolutePath), $path);

                continue;
            }

            $glob = $absolutePath;
            if (is_dir($absolutePath)) {
                $glob = sprintf('%s/*', $absolutePath);
            }

            foreach (Glob::glob($glob) as $file) {
                if (!is_file($file)) {
                    continue;
                }

                $files[] = new File($file, md5_file($file), $path);
            }
        }

        return $files;
    }

    public function addPath(string $path): void
    {
        $this->paths[] = rtrim($path, '/');
    }

    /**
     * Returns a list of files or directories. Directories DOES not have a trailing slash
     *
     * @return list<string>
     */
    public function getPaths(): array
    {
        return $this->paths;
    }
}
