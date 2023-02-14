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
        $configuration->addPath('root/dir1'); // This will only resolve to files in this directory
        $configuration->addPath('root/dir2/**/*'); // This will resolve to all files (recursively) under this directory
        $configuration->addPath('root/file3');

        $files = $configuration->resolveFiles(__DIR__);
        self::assertCount(4, $files);

        $expectedFilenames = [
            __DIR__ . '/root/dir1/file1',
            __DIR__ . '/root/dir2/dir3/file2',
            __DIR__ . '/root/dir2/file4',
            __DIR__ . '/root/file3',
        ];

        for ($i = 0; $i < 4; ++$i) {
            $file = $files[$i];
            self::assertSame($expectedFilenames[$i], $file->filename);
        }
    }
}
