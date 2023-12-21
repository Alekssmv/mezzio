<?php

declare(strict_types=1);

namespace App\Handler;

use AmoCRM\Client\AmoCRMApiClient;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\Diactoros\Response\HtmlResponse;
use Mezzio\Template\TemplateRendererInterface;
use App\Helper\TokenActions;
use Exception;

class RedirectUriHandler implements RequestHandlerInterface
{
    public function __construct(
        private TemplateRendererInterface $renderer,
        private AmoCRMApiClient $apiClient,
    ) {
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
            echo $e->getMessage();
        }
        return new HtmlResponse(
            $this->renderer->render(
                'app::redirect-uri', $data
            )
        );
    }
}
