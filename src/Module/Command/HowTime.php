<?php

namespace Module\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Carbon\Carbon;

/**
 * Команда для отображения текущего времени
 */
class HowTime extends Command
{
    protected static $defaultName = 'app:how-time';

    /**
     * Конфигурирование команды
     */
    protected function configure(): void
    {
        $this->setDescription('Показывает текущее время');
    }

    /**
     * Выполнение команды, вывод текущего времени в формате 'Now time: 12:34 (01.2022)'
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Now time: ' . Carbon::now()->format('H:i (m.Y)'));

        return Command::SUCCESS;
    }
}
