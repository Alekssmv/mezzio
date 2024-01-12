<?php

namespace Module\Config;

use Pheanstalk\Pheanstalk;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class Beanstalk
{
    /** @var Pheanstalk|null */
    private ?Pheanstalk $connection;

    /** @var array */
    private array $config;

    /**
     * Constructor Beanstalk
     */
    public function __construct(ContainerInterface $container)
    {
        try {
            $this->config = $container->get('config')['beanstalk'];
            $this->connection = Pheanstalk::create(
                $this->config['host'],
                $this->config['port'],
                $this->config['timeout']
            );
        } catch (NotFoundExceptionInterface | ContainerExceptionInterface $e) {
            exit($e->getMessage());
        }
    }

    /**
     * Возвращает pheanstalk-соединение
     * @return Pheanstalk|null
     */
    public function getConnection(): ?Pheanstalk
    {
        return $this->connection;
    }
}
