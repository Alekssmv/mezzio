<?php

declare(strict_types=1);

namespace App\Handler;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Laminas\Diactoros\Response\JsonResponse;
use AmoCRM\Client\AmoCRMApiClient;
use App\Helper\TokenActions;
use Exception;

/*
 * Маршрут для получения списка контактов
 * Принимает url параметр account_id
 * Возвращает ошибку, если account_id не передан
 * Возвращает ошибку, если токен не найден
 * Возвращает ошибку, если токен истек
 * Возвращает ошибку, если нет контактов
 * Возвращает список контактов в формате JSON
 */
class GetContactsHandler implements RequestHandlerInterface
{
    private AmoCRMApiClient $apiClient;
    public function __construct(
        AmoCRMApiClient $apiClient
    ) {
        $this->apiClient = $apiClient;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $params = $request->getQueryParams();
        $apiClient = $this->apiClient;

        try {
            if (!isset($params['account_id'])) {
                throw new Exception('account_id is not set');
            }

            $accessToken = TokenActions::getToken((int) $params['account_id']);
            if ($accessToken === null) {
                throw new Exception('token not found');
            }
            if ($accessToken->hasExpired()) {
                throw new Exception('token expired');
            }

            $baseDomain = $accessToken->getValues()['baseDomain'];
            $apiClient->setAccessToken($accessToken)->setAccountBaseDomain($baseDomain);
            $contacts = $apiClient->contacts()->get();
        } catch (Exception $e) {
            var_dump($e->getMessage());
            die();
        }

        try {
            if (empty($contacts)) {
                throw new Exception('contacts not found');
            }
        } catch (Exception $e) {
            var_dump($e->getMessage());
            die();
        }

        return new JsonResponse($contacts);
    }
}
