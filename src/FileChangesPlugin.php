<?php

declare(strict_types=1);

namespace Setono\Composer\FileChanges;

use Composer\Composer;
use Composer\Factory;
use Composer\IO\IOInterface;
use Composer\Json\JsonFile;
use Composer\Plugin\Capability\CommandProvider;
use Composer\Plugin\Capable;
use Composer\Plugin\PluginInterface;
use Setono\Composer\FileChanges\Command\LockCommand;
use Setono\Composer\FileChanges\Configuration\ComposerExtraConfigurationResolver;

final class FileChangesPlugin implements PluginInterface, Capable, CommandProvider
{
    public function activate(Composer $composer, IOInterface $io): void
    {
    }

    public function deactivate(Composer $composer, IOInterface $io): void
    {
    }

    public function uninstall(Composer $composer, IOInterface $io): void
    {
    }

    public function getCapabilities(): array
    {
        return [
            CommandProvider::class => self::class,
        ];
    }

    public function getCommands(): array
    {
        $composerFile = Factory::getComposerFile();
        $composerLock = 'json' === pathinfo($composerFile, \PATHINFO_EXTENSION) ? substr($composerFile, 0, -4) . 'lock' : $composerFile . '.lock';
        $fileChangesLock = str_replace('composer', 'file-changes', basename($composerLock));

        return [
            new LockCommand(new Locker(new JsonFile($fileChangesLock), new ComposerExtraConfigurationResolver(new ComposerData($composerFile)))),
        ];
    }
}
