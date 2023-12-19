<?php

declare(strict_types=1);

namespace App\Handler;

use AmoCRM\Client\AmoCRMApiClient;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\Diactoros\Response\HtmlResponse;
use Mezzio\Template\TemplateRendererInterface;

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
        try {
            $accessToken = getToken();
            $ownerDetails = $this->apiClient->getOAuthClient()->getResourceOwner($accessToken);
            $data = $ownerDetails->toArray();
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
        return new HtmlResponse($this->renderer->render(
            'app::redirect-uri', $data
        ));
    }
}
