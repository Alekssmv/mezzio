<?php

declare(strict_types=1);

namespace App\Handler;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Module\Config\Beanstalk as BeanstalkConfig;
use Pheanstalk\Pheanstalk;

/**
 * Принимает параметры unisender_key и account_id
 * Создает запись в таблице accounts, если ее еще нет.
 * Если запись уже есть, то добавляет unisender_key в запись аккаунта
 */
class GetUniApiKeyHandler implements RequestHandlerInterface
{
    /**
     * @var Pheanstalk - подключение к beanstalk
     */
    private Pheanstalk $beanstalk;

    public function __construct(
        BeanstalkConfig $beanstalkConfig
    ) {
        $this->beanstalk = $beanstalkConfig->getConnection();
    }
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $params = $request->getParsedBody();

        $this->beanstalk->useTube('unisender-api-key')->put(json_encode($params));

        return new JsonResponse([
            'success' => true,
            'message' => 'Задача на добавление unisender api key в очередь успешно добавлена'
        ]);
    }
}
