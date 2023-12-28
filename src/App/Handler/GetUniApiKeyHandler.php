<?php

declare(strict_types=1);

namespace App\Handler;

use AmoCRM\Client\AmoCRMApiClient;
use App\Services\AccountService;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Exception;

/**
 * Принимает параметры unisender_key и account_id
 * Создает запись в таблице accounts, если ее еще нет.
 * Если запись уже есть, то добавляет unisender_key в запись аккаунта
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
        $params = $request->getParsedBody();

        if (!isset($params['unisender_key']) && !isset($params['account_id'])) {
            return new JsonResponse([
                'error' => 'unisender_key and account_id are required',
            ], 400);
        }

        try {
            $account = $this->accountService->findOrCreate((int) $params['account_id']);
            $account = $this->accountService->addUnisenderApiKey((int) $params['account_id'], $params['unisender_key']);
        } catch (Exception $e) {
            return new JsonResponse([
                'error' => $e->getMessage(),
            ]);
        }
        
        return new JsonResponse([
            'status' => 'success',
            'data' => [
                'unisender_key' => $account->unisender_api_key,
                'account_id' => $account->account_id,
            ],
        ]);
    }
}
