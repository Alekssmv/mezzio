<?php

namespace Module\Worker;

use Pheanstalk\Pheanstalk;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;

/**
 * Воркер для обработки задач по выводу времени
 */
class Time extends Command
{
    /**
     * Название очереди
     */
    public const QUEUE = 'times';

    /**
     * Название команды
     */
    protected static $defaultName = 'app:time-worker';

    /**
     * @var Pheanstalk
     */
    private $worker;

    public function __construct()
    {
        parent::__construct();

        $this->worker = Pheanstalk::create('103.106.2.148', 11300)->watch(self::QUEUE);
    }

    /**
     * Конфигурация команды, создание описания
     */
    protected function configure(): void
    {
        $this->setDescription('Воркер для обработки задач по выводу времени');
    }

    /**
     * Выполнение команды
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        while (true) {
            $job = $this->worker->reserveWithTimeout(0);
            if (!$job) {
                $output->writeln('Нет задач для обработки');
                return Command::SUCCESS;
            }
            $processedData = $this->process($job->getData());
            $output->writeln($processedData);
            $this->worker->delete($job);
        }
    }

    /**
     * Фоматирует время в формат 'Now time: 12:34 (01.2025)'
     */
    public function process(string $data): string
    {
        if ($data === 'now') {
            $data = time();
        }
        $time = date('H:i (m.Y)', (int) $data);
        return "Now time: {$time}\n";
    }
}
