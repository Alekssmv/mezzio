<?php

namespace Module\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Команда для отображения текущего времени
 */
class HowTime extends Command
{
    protected static $defaultName = 'app:how-time';

    protected function configure(): void
    {
        $this->setDescription('Показывает текущее время');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln(date('Y-m-d H:i:s'));

        return Command::SUCCESS;
    }
}
