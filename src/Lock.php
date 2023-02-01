<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Setono\Composer\FileChanges;

use Composer\Json\JsonFile;

final class Lock
{
    private JsonFile $json;

    /**
     * @var array<string, array>
     */
    private array $lock = [];

    private bool $changed = false;

    public function __construct($lockFile)
    {
        $this->json = new JsonFile($lockFile);
        if ($this->json->exists()) {
            $this->lock = $this->json->read();
        }
    }

    public function has(string $name): bool
    {
        return \array_key_exists($name, $this->lock);
    }

    public function add(string $name, array $data): void
    {
        $current = $this->lock[$name] ?? [];
        $this->lock[$name] = array_merge($current, $data);
        $this->changed = true;
    }

    public function get(string $name): ?array
    {
        return $this->lock[$name] ?? null;
    }

    public function set(string $name, array $data): void
    {
        if (!\array_key_exists($name, $this->lock) || $data !== $this->lock[$name]) {
            $this->lock[$name] = $data;
            $this->changed = true;
        }
    }

    public function remove(string $name): void
    {
        if (\array_key_exists($name, $this->lock)) {
            unset($this->lock[$name]);
            $this->changed = true;
        }
    }

    public function write(): void
    {
        if (!$this->changed) {
            return;
        }

        if ($this->lock) {
            ksort($this->lock);
            $this->json->write($this->lock);
        } elseif ($this->json->exists()) {
            @unlink($this->json->getPath());
        }
    }

    public function all(): array
    {
        return $this->lock;
    }
}
