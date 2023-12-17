<?php

declare(strict_types=1);

namespace App\Client;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\Diactoros\Response\HtmlResponse;
use Mezzio\Template\TemplateRendererInterface;
use AmoCRM\Client\AmoCRMApiClient;

class ApiClient implements RequestHandlerInterface
{
    public function __construct(private AmoCRMApiClient $apiClient)
    {
    }

    public function handle(ServerRequestInterface $request) : AmoCRMApiClient
    {
        return $this->apiClient;
    }
}
