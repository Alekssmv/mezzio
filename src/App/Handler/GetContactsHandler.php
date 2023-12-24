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
                return new JsonResponse(['error' => 'account_id is required']);
            }

            $accessToken = TokenActions::getToken($params['account_id']);
            if ($accessToken === null) {
                return new JsonResponse(['error' => 'token not found']);
            }
            if ($accessToken->hasExpired()) {
                return new JsonResponse(['error' => 'token expired']);
            }

            $baseDomain = $accessToken->getValues()['baseDomain'];
            $apiClient->setAccessToken($accessToken)->setAccountBaseDomain($baseDomain);
            $contacts = $apiClient->contacts()->get();

        } catch (Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()]);
        }

        return new JsonResponse($contacts);
    }
}
