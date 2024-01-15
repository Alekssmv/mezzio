<?php

namespace Module\Worker;

use Module\Worker\BaseWorker;

/**
 * Воркер для обработки задач по выводу времени
 */
class Time extends BaseWorker
{
    /**
     * @var string $data - данные для обработки
     */
    public function process($data): void
    {
        if ($data === 'now') {
            $data = time();
        }
        $time = date('H:i (m.Y)', (int) $data);
        echo "Now time: {$time}\n";
    }

    /**
     * Добавляем описание команды
     */
    public function configure(): void
    {
        $this->setDescription('Воркер для обработки задач по выводу времени');
    }
}
