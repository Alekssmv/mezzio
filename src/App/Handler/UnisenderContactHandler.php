<?php

declare(strict_types=1);

namespace App\Handler;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Unisender\ApiWrapper\UnisenderApi;
use AmoCRM\Client\AmoCRMApiClient;

class UnisenderContactHandler implements RequestHandlerInterface
{
    private UnisenderApi $unisenderApiClient;
    private AmoCRMApiClient $amoCRMApiClient;
    public function __construct(
        UnisenderApi $unisenderApiClient,
        AmoCRMApiClient $amoCRMApiClient
    ) {
        $this->unisenderApiClient = $unisenderApiClient;
        $this->amoCRMApiClient = $amoCRMApiClient;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $amoCRMApiClient = $this->amoCRMApiClient;
        $unisenderApiClient = $this->unisenderApiClient;

        $contact = $unisenderApiClient->getContact(['email' => 'test@test.com']);
        dd($contact);
    }
}
