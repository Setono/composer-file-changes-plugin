<?php

declare(strict_types=1);

namespace Setono\Composer\FileChanges\Tests\Configuration;

use PHPUnit\Framework\TestCase;
use Setono\Composer\FileChanges\Configuration\Configuration;

final class ConfigurationTest extends TestCase
{
    /**
     * @test
     */
    public function it_resolves(): void
    {
        $configuration = new Configuration();
        $configuration->paths[] = 'root/dir';
        $configuration->paths[] = 'root/file3';

        $files = $configuration->resolveFiles(__DIR__);
        self::assertEquals([
            __DIR__ . '/root/dir/dir2/dir3/file2',
            __DIR__ . '/root/dir/dir1/file1',
            __DIR__ . '/root/file3',
        ], $files);
    }
}
