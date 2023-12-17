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
            $response = $this->httpClient->request('GET', 'https://0f7e-5-182-36-14.ngrok-free.app/api/v1/token?code=' . $_GET['code']);
            dd($response->getBody()->getContents());
            
            saveToken(
                [
                    'accessToken' => $json['access_token'],
                    'refreshToken' => $json['refresh_token'],
                    'expires' => $json['expires_in'],
                    'baseDomain' => $this->apiClient->getAccountBaseDomain(),
                ]
            );
            $accessToken = getToken();
            $ownerDetails = $this->apiClient->getOAuthClient()->getResourceOwner($accessToken);
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
        return new HtmlResponse($this->renderer->render(
            'app::redirect-uri',
            [
                'ownerDetails' => $ownerDetails->toArray()
            ]
        ));
    }
}
