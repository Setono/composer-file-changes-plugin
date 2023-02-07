<?php

declare(strict_types=1);

namespace Setono\Composer\FileChanges\Command;

use Composer\Command\BaseCommand;
use Setono\Composer\FileChanges\Locker;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @interal
 */
final class LockCommand extends BaseCommand
{
    public function __construct(private readonly Locker $locker)
    {
        parent::__construct();
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
        $this->locker->update();

        return self::SUCCESS;
    }
}
