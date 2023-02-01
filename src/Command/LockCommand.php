<?php

declare(strict_types=1);

namespace Setono\Composer\FileChanges\Command;

use Composer\Command\BaseCommand;
use Setono\Composer\FileChanges\FileChangesPlugin;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Webmozart\Assert\Assert;

/**
 * @interal
 */
final class LockCommand extends BaseCommand
{
    private FileChangesPlugin $plugin;

    public function __construct(FileChangesPlugin $plugin)
    {
        parent::__construct();

        $this->plugin = $plugin;
    }

    protected function configure(): void
    {
        $this
            ->setName('setono:file-changes:lock')
            ->setDescription('Will update the file-changes.lock')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = $this->getIO();

        $composer = $this->requireComposer();

        try {
            $files = self::filesFromExtra($composer->getPackage()->getExtra());
        } catch (\Throwable $exception) {
            $io->writeError(sprintf('<error>%s</error>', $exception->getMessage()));

            return self::FAILURE;
        }

        $lock = $this->plugin->getLock();

        foreach ($files as $file) {
            $lock->add($file, [
                'hash' => md5_file($file),
            ]);
        }

        $lock->write();

        return self::SUCCESS;
    }

    /**
     * @return list<string>
     */
    private static function filesFromExtra(array $extra): array
    {
        if (!array_key_exists('composer-file-changes', $extra)) {
            return [];
        }

        $configuration = $extra['composer-file-changes'];
        Assert::isArray($configuration);

        Assert::keyExists($configuration, 'files');
        $files = $configuration['files'];
        Assert::isList($files);
        Assert::allStringNotEmpty($files);
        Assert::allFileExists($files);

        return $files;
    }
}
