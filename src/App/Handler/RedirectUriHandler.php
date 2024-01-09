<?php

declare(strict_types=1);

namespace App\Handler;

use AmoCRM\Client\AmoCRMApiClient;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\Diactoros\Response\HtmlResponse;
use Mezzio\Template\TemplateRendererInterface;
use App\Helper\TokenActions;
use Exception;

class RedirectUriHandler implements RequestHandlerInterface
{
    private AmoCRMApiClient $apiClient;
    private TemplateRendererInterface $templateRenderer;
    public function __construct(
        TemplateRendererInterface $renderer,
        AmoCRMApiClient $apiClient
    ) {
        $this->apiClient = $apiClient;
        $this->renderer = $renderer;
    }
    /**
     * Берет токен из файла TOKEN_FILE
     * Возвращает страницу с именем пользователя
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $params = $request->getQueryParams();
        try {
            if (!TokenActions::isTokenExist((int) $params['account_id'])) {
                exit('Access token file not found');
            }
            $accessToken = TokenActions::getToken((int) $params['account_id']);
            $ownerDetails = $this->apiClient->getOAuthClient()->getResourceOwner($accessToken);
            $data = $ownerDetails->toArray();
        } catch (Exception $e) {
            return new JsonResponse([
                'error' => $e->getMessage(),
            ]);
        }
        
        return new HtmlResponse(
            $this->renderer->render(
                'app::redirect-uri',
                $data
            )
        );
    }
}
