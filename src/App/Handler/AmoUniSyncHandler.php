<?php

declare(strict_types=1);

namespace App\Handler;

use AmoCRM\Client\AmoCRMApiClient;
use App\Services\AccountService;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Unisender\ApiWrapper\UnisenderApi;
use App\Services\ContactFormatterService;
use Exception;
use League\OAuth2\Client\Token\AccessToken;
use App\Services\ContactService;
use App\Helper\ArrayHelper;
use App\Services\EmailEnumService;
use Pheanstalk\Pheanstalk;
use Module\Config\Beanstalk as BeanstalkConfig;

class AmoUniSyncHandler implements RequestHandlerInterface
{
    /**
     * @var UnisenderApi
     */
    private $unisenderApi;

    /**
     * @var ContactFormatterService
     */
    private $contactFormatterService;

    /**
     * @var AccountService
     */
    private $accountService;

    /**
     * @var AmoCRMApiClient
     */
    private $amoApiClient;

    /**
     * @var ContactService
     */
    private $contactService;

    /**
     * @var EmailEnumService
     */
    private $emailEnumService;

    /**
     * @var Pheanstalk - подключение к beanstalk
     */
    private $beanstalk;

    public function __construct(
        // UnisenderApi $unisenderApi,
        // ContactFormatterService $contactFormatterService,
        // AccountService $accountService,
        // AmoCRMApiClient $amoApiClient,
        // ContactService $contactService,
        // EmailEnumService $emailEnumService,
        BeanstalkConfig $beanstalkConfig
    ) {
        // $this->unisenderApi = $unisenderApi;
        // $this->contactFormatterService = $contactFormatterService;
        // $this->accountService = $accountService;
        // $this->amoApiClient = $amoApiClient;
        // $this->contactService = $contactService;
        // $this->emailEnumService = $emailEnumService;
        $this->beanstalk = $beanstalkConfig->getConnection();
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $params = $request->getParsedBody();
        $beanstalk = $this->beanstalk;

        $beanstalk->useTube('contacts-sync')->put(json_encode($params));
        return new JsonResponse(['success' => 'Job to sync contacts was added to queue'], 200);
    }
}
