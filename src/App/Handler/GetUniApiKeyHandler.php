<?php

declare(strict_types=1);

namespace App\Handler;

use AmoCRM\Client\AmoCRMApiClient;
use App\Services\AccountService;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Принимает параметры unisender_key и account_id
 * Создает запись в таблице accounts
 */
class GetUniApiKeyHandler implements RequestHandlerInterface
{
    /**
     * @var AmoCRMApiClient - клиент для работы с API amoCRM
     */
    private AmoCRMApiClient $apiClient;

    /**
     * @var AccountService - сервис для работы с аккаунтами
     */
    private AccountService $accountService;

    public function __construct(
        AmoCRMApiClient $apiClient,
        AccountService $accountService
    ) {
        $this->apiClient = $apiClient;
        $this->accountService = $accountService;
    }
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $params = $request->getBody()->getContents();
        $params = json_decode($params, true);
        
        if (!isset($params['unisender_key']) && !isset($params['account_id'])) {
            die('Unisender key or account id is not set');
        }

        $account = $this->accountService->create([
            'unisender_key' => $params['unisender_key'],
            'account_id' => $params['account_id'],
        ]);

        return new JsonResponse([
            'status' => 'success',
            'data' => [
                'unisender_key' => $account->unisender_key,
                'account_id' => $account->account_id,
            ],
        ]);
    }
}
