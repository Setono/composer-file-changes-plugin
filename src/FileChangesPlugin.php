<?php

declare(strict_types=1);

namespace Setono\Composer\FileChanges;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\Factory;
use Composer\IO\IOInterface;
use Composer\Json\JsonFile;
use Composer\Plugin\Capability\CommandProvider;
use Composer\Plugin\Capable;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;
use Composer\Util\ProcessExecutor;
use Setono\Composer\FileChanges\Command\LockCommand;
use Setono\Composer\FileChanges\Configuration\ComposerExtraConfigurationResolver;

final class FileChangesPlugin implements PluginInterface, Capable, CommandProvider, EventSubscriberInterface
{
    public function activate(Composer $composer, IOInterface $io): void
    {
        // some of this logic is copied from Symfony Flex plugin
        $win = '\\' === \DIRECTORY_SEPARATOR;

        $exception = new \RuntimeException('Cannot activate file changes plugin: git not found.');

        $output = exec($win ? 'where git' : 'command -v git');
        if (false === $output) {
            throw $exception;
        }

        $command = strtok($output, \PHP_EOL);
        if (false === $command) {
            throw $exception;
        }

        if (!is_executable($command)) {
            throw $exception;
        }
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
            new LockCommand(
                new Locker(
                    new JsonFile(self::getLockFilename()),
                    new ComposerExtraConfigurationResolver(new ComposerData(self::getComposerFilename())),
                ),
            ),
        ];
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ScriptEvents::PRE_UPDATE_CMD => 'preUpdate',
            ScriptEvents::POST_UPDATE_CMD => 'postUpdate',
        ];
    }

    public function preUpdate(Event $event): void
    {
        $lockFile = new JsonFile(self::getLockFilename());
        $locker = new Locker(
            $lockFile,
            new ComposerExtraConfigurationResolver(new ComposerData(self::getComposerFilename())),
        );

        $exists = $lockFile->exists();

        $locker->update();

        if ($this->hasLockFileChanged($event->getIO())) {
            throw new \RuntimeException(sprintf('The file-changes.lock has been %s. Commit this file to version control before updating.', $exists ? 'created' : 'updated'));
        }
    }

    public function postUpdate(Event $event): void
    {
        $locker = new Locker(
            new JsonFile(self::getLockFilename()),
            new ComposerExtraConfigurationResolver(new ComposerData(self::getComposerFilename())),
        );
        $locker->update();

        if ($this->hasLockFileChanged($event->getIO())) {
            $event->getIO()->error('The file-changes.lock has changed. Review these changes and act accordingly.');
        }
    }

    private static function getComposerFilename(): string
    {
        return Factory::getComposerFile();
    }

    private static function getLockFilename(): string
    {
        // this logic is copied from Symfony Flex
        $composerFile = self::getComposerFilename();
        $composerLock = 'json' === pathinfo($composerFile, \PATHINFO_EXTENSION) ? substr($composerFile, 0, -4) . 'lock' : $composerFile . '.lock';

        return str_replace('composer', 'file-changes', basename($composerLock));
    }

    private function hasLockFileChanged(IOInterface $io): bool
    {
        $output = '';

        $processExecutor = new ProcessExecutor($io);
        $processExecutor->execute('git status --porcelain', $output);

        /** @psalm-suppress MixedArgument,PossiblyNullArgument */
        $output = trim($output);

        if ('' === $output) {
            return false;
        }

        $files = explode(\PHP_EOL, $output);
        foreach ($files as $file) {
            if (str_ends_with($file, 'file-changes.lock')) {
                return true;
            }
        }

        return false;
    }
}
