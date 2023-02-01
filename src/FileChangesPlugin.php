<?php
declare(strict_types=1);

namespace Setono\Composer\FileChanges;

use Composer\Composer;
use Composer\Factory;
use Composer\IO\IOInterface;
use Composer\Plugin\Capability\CommandProvider;
use Composer\Plugin\Capable;
use Composer\Plugin\PluginInterface;
use Setono\Composer\FileChanges\Command\LockCommand;

final class FileChangesPlugin implements PluginInterface, Capable, CommandProvider
{
    private Lock $lock;

    public function activate(Composer $composer, IOInterface $io): void
    {
        $composerFile = Factory::getComposerFile();
        $composerLock = 'json' === pathinfo($composerFile, \PATHINFO_EXTENSION) ? substr($composerFile, 0, -4).'lock' : $composerFile.'.lock';
        $fileChangesLock = str_replace('composer', 'file-changes', basename($composerLock));

        $this->lock = new Lock($fileChangesLock);

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
        return [
            new LockCommand($this),
        ];
    }

    public function getLock(): Lock
    {
        return $this->lock;
    }
}
