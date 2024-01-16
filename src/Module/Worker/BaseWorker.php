<?php

namespace Module\Worker;

use Pheanstalk\Contract\PheanstalkInterface;
use Pheanstalk\Job;
use Pheanstalk\Pheanstalk;
use Module\Config\Beanstalk as BeanstalkConfig;
use Throwable;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class BaseWorker extends Command
{
    /** @var Pheanstalk The Pheanstalk connection. */
    protected Pheanstalk $connection;

    /** @var string The default name of command. */
    protected static $defaultName = 'worker';

    /** @var string The queue name. */
    protected string $queue = 'default';

    /**
     * Constructor BaseWorker
     *
     * @param BeanstalkConfig $beanstalk
     */
    public function __construct(BeanstalkConfig $beanstalk, string $queue)
    {
        parent::__construct();
        $this->queue = $queue;
        $this->connection = $beanstalk->getConnection();
    }

    /** Executes CLI */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        while (
            $job = $this->connection
            ->watchOnly($this->queue)
            ->ignore(PheanstalkInterface::DEFAULT_TUBE)
            ->reserveWithTimeout(2)
        ) {
            try {
                $this->process(json_decode(
                    $job->getData(),
                    true,
                    512,
                    JSON_THROW_ON_ERROR
                ));
            } catch (Throwable $exception) {
                $this->handleException($exception, $job);
            }
            $this->connection->delete($job);
        }
        echo "No jobs left in queue {$this->queue}" . PHP_EOL;
        return Command::SUCCESS;
    }
    /**
     * @param Throwable $exception
     * @param Job $job
     */
    private function handleException(Throwable $exception, Job $job): void
    {
        echo "Error: Unhandled exception $exception" . PHP_EOL . $job->getData();
    }

    /** Abstract method to process data. */
    abstract public function process($data): void;
}
