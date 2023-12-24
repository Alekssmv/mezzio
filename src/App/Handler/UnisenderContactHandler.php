<?php

declare(strict_types=1);

namespace App\Handler;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Unisender\ApiWrapper\UnisenderApi;

/*
* Маршрут для получения созданного мной контакта в Unisender
* Возвращает контакт в формате JSON
*/
class UnisenderContactHandler implements RequestHandlerInterface
{
    /**
     * Переменная для работы с API Unisender
     * @var UnisenderApi
     */
    private UnisenderApi $unisenderApiClient;
    public function __construct(
        UnisenderApi $unisenderApiClient
    ) {
        $this->unisenderApiClient = $unisenderApiClient;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $unisenderApiClient = $this->unisenderApiClient;

        $contact = $unisenderApiClient->getContact(['email' => 'test@test.com']);
        return new JsonResponse($contact);
    }
}
