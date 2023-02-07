<?php

declare(strict_types=1);

namespace Setono\Composer\FileChanges\Tests;

use PHPUnit\Framework\TestCase;
use Setono\Composer\FileChanges\ComposerData;

final class ComposerDataTest extends TestCase
{
    /**
     * @test
     */
    public function it_returns_extra(): void
    {
        $composerData = new ComposerData(__DIR__ . '/test-composer.json');

        self::assertEquals([
            'file-changes' => [
                'paths' => [
                    'vendor/path/filename',
                ],
            ],
        ], $composerData->getExtra());
    }
}
