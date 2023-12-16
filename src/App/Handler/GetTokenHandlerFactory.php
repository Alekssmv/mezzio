<?php

declare(strict_types=1);

namespace App\Handler;

use Psr\Container\ContainerInterface;
use GuzzleHttp\Client;
use AmoCRM\Client\AmoCRMApiClient;
use App\Handler\GetTokenHandler;


class GetTokenHandlerFactory
{
    public function __invoke(ContainerInterface $container) : GetTokenHandler
    {
        $apiClient = new AmoCRMApiClient(
            '9352e273-23bb-4028-8844-e0e196bb2f19',
            '18p74Xvk6MTvzDguH3PH6Bfh648Odb9CnFUIuVbOYQvepMXMoCyRnALE0VTcgWbQ',
            'https://webhook.site/#!/3998a479-aa49-49b7-afb5-52dfb082c57b',
        );
        $apiClient->setAccountBaseDomain('testasmirnov.amocrm.ru');
        $httpClient = new Client();

        return new GetTokenHandler($apiClient, $httpClient);
    }
}
