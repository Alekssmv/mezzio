<?php

declare(strict_types=1);

namespace App\Handler;

use AmoCRM\Client\AmoCRMApiClient;
use GuzzleHttp\Client;
use League\OAuth2\Client\Grant\RefreshToken;
use League\OAuth2\Client\Token\AccessToken;
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
            $this->apiClient->setAccountBaseDomain('testasmirnov.amocrm.ru');
            // Get the public url of the tunnel
            $response = $this->httpClient->request('GET', 'https://api.ngrok.com/tunnels', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $_ENV["NGROK_API_TOKEN"],
                    'Ngrok-Version' => '2'
                ]
            ]);
            $url = json_decode($response->getBody()->getContents(), true)['tunnels'][0]['public_url'];
            // Get token
            $accessToken = $this->httpClient->request('GET', $url . '/api/v1/token?code=' . $_GET['code'])->getBody()->getContents();
            $accessToken = json_decode($accessToken, true);
            if ($accessToken === null) {
                throw new \Exception('Не получилось получить токен');
            }
            // Save token
            saveToken([
                'accessToken' => $accessToken['access_token'],
                'refreshToken' => $accessToken['refresh_token'],
                'expires' => $accessToken['expires'],
                'baseDomain' => $this->apiClient->getAccountBaseDomain(),
            ]);
            // Get token
            $accessToken = getToken();
            // Get owner details
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
