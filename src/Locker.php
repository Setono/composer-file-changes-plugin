<?php

declare(strict_types=1);

namespace Setono\Composer\FileChanges;

use Composer\Json\JsonFile;
use Setono\Composer\FileChanges\Configuration\ConfigurationResolverInterface;

final class Locker
{
    private bool $changed = false;

    /** @var array<string, array> */
    private array $lock = [];

    public function __construct(
        private readonly JsonFile $lockFile,
        private readonly ConfigurationResolverInterface $configurationResolver,
    ) {
        if ($this->lockFile->exists()) {
            /** @var array<string, array> $lock */
            $lock = $this->lockFile->read();
            $this->lock = $lock;
        }
    }

    public function update(): void
    {
        $configuration = $this->configurationResolver->resolve();

        $files = $configuration->resolveFiles($this->lockFile->getPath());
        foreach ($files as $file) {
            $this->add($file, [
                'hash' => md5_file($file),
            ]);
        }

        $this->write();
    }

    public function add(string $filename, array $data): void
    {
        // todo this is probably not the best place to fix the absolute to relative path conversion
        $filename = ltrim(str_replace($this->lockFile->getPath(), '', $filename), '/');

        $current = $this->lock[$filename] ?? [];
        $this->lock[$filename] = array_merge($current, $data);
        $this->changed = true;
    }

    public function write(): void
    {
        if (!$this->changed) {
            return;
        }

        if ($this->lock) {
            ksort($this->lock);
            $this->lockFile->write($this->lock);
        } elseif ($this->lockFile->exists()) {
            @unlink($this->lockFile->getPath());
        }
    }
}
