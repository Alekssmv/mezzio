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

class GetContactsHandler implements RequestHandlerInterface
{
    public function __construct(private AmoCRMApiClient $apiClient
    ) {
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
            $baseDomain = $accessToken->getValues()['baseDomain'];
            $apiClient->setAccessToken($accessToken)->setAccountBaseDomain($baseDomain);
            dd($apiClient->contacts()->get());
            

        } catch (Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()]);
        }


        return new JsonResponse(['success' => 'ok']);
    }
}