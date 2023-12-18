<?php

declare(strict_types=1);

namespace App\Handler;

use AmoCRM\Client\AmoCRMApiClient;
use GuzzleHttp\Client;
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
        private Client $httpClient,
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $this->httpClient->request('GET', 'https://05ab-5-182-36-14.ngrok-free.app' . '/api/v1/token?code=' . $_GET['code']);
            
            $accessToken = getToken();

            $ownerDetails = $this->apiClient->getOAuthClient()->getResourceOwner($accessToken);
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
        $data = $ownerDetails->toArray();
        return new HtmlResponse($this->renderer->render(
            'app::redirect-uri', $data
        ));
    }
}
