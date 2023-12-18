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
            // Get the public url of the tunnel
            $response = $this->httpClient->request('GET', 'https://api.ngrok.com/tunnels', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $_ENV["NGROK_API_TOKEN"],
                    'Ngrok-Version' => '2'
                ]
            ]);
            $url = json_decode($response->getBody()->getContents(), true)['tunnels'][0]['public_url'];
            // Save the access token to the file
            $this->httpClient->request('GET', $url . '/api/v1/token?code=' . $_GET['code']);

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
