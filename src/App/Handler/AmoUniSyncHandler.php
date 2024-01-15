<?php

declare(strict_types=1);

namespace App\Handler;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Pheanstalk\Pheanstalk;
use Module\Config\Beanstalk as BeanstalkConfig;

class AmoUniSyncHandler implements RequestHandlerInterface
{
    /**
     * @var Pheanstalk - подключение к beanstalk
     */
    private $beanstalk;

    public function __construct(
        BeanstalkConfig $beanstalkConfig
    ) {
        $this->beanstalk = $beanstalkConfig->getConnection();
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $params = $request->getParsedBody();
        $beanstalk = $this->beanstalk;

        $beanstalk->useTube('contacts-sync')->put(json_encode($params));
        return new JsonResponse(['success' => 'Job to sync contacts was added to queue'], 200);
    }
}
