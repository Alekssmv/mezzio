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
use App\Services\ContactsService;
use Exception;
use League\OAuth2\Client\Token\AccessToken;

class AmoUniSyncHandler implements RequestHandlerInterface
{
    private const ACTIONS = [
        'add' => 'add',
        'update' => 'update',
        'delete' => 'delete',
    ];

    /**
     * @var UnisenderApi
     */
    private $unisenderApi;

    /**
     * @var ContactsService
     */
    private $contactsService;

    /**
     * @var AccountService
     */
    private $accountService;

    /**
     * @var AmoCRMApiClient
     */
    private $amoApiClient;

    public function __construct(
        UnisenderApi $unisenderApi,
        ContactsService $contactsService,
        AccountService $accountService,
        AmoCRMApiClient $amoApiClient
    ) {
        $this->unisenderApi = $unisenderApi;
        $this->contactsService = $contactsService;
        $this->accountService = $accountService;
        $this->amoApiClient = $amoApiClient;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        /**
         * @var AmoCRMApiClient $amoApiClient
         */
        $amoApiClient = $this->amoApiClient;

        /**
         * @var UnisenderApi $unisenderApi
         */
        $unisenderApi = $this->unisenderApi;

        /**
         * @var ContactsService $contactsService
         */
        $contactsService = $this->contactsService;
        $uniApiKey = null;
        $contacts = [];
        $contactsToDel = [];
        $params = $request->getParsedBody();

        if (!isset($params['account']) && !isset($params['contacts'])) {
            return new JsonResponse(['error' => 'Invalid request'], 400);
        }

        try {
            $account = $this->accountService->findByAccountId((int) $params['account']['id']);
            $uniApiKey = $account->unisender_api_key;
            $accessToken = $account->amo_access_jwt;
            $json = json_decode($accessToken, true);
            $accessToken = new AccessToken(
                [
                    'access_token' => $json['accessToken'],
                    'refresh_token' => $json['refreshToken'],
                    'expires' => $json['expires'],
                    'base_domain' => $json['baseDomain']
                ]
            );
            $amoApiClient->setAccessToken($accessToken);
            $amoApiClient->setAccountBaseDomain($accessToken->getValues()['base_domain']);
        } catch (Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 400);
        }

        foreach ($params['contacts'] as $action => $contacts) {
            // if ($action === 'delete') {
            //     $contactsToDel = $contactsService->formatContacts($contacts, CUSTOM_FIELD_NAMES, FIELDS, FIELDS_MULTI_VAL);
            //     $contactsToDel = $contactsService->filterContacts($contacts, REQ_FIELDS);
            //     $contactsToDel = $contactsService->dublicateContacts($contacts, REQ_FIELDS);
            // }
            $contacts = $contactsService->formatContacts($contacts, CUSTOM_FIELD_NAMES, FIELDS, FIELDS_MULTI_VAL);
            $contacts = $contactsService->filterContacts($contacts, REQ_FIELDS);
            $contacts = $contactsService->dublicateContacts($contacts, REQ_FIELDS);
        }

        $fieldNames = $contactsService->getFieldNames(CUSTOM_FIELD_NAMES, FIELDS, FIELDS_MULTI_VAL);
        $data = $contactsService->getDataForUnisender($contacts, $fieldNames);

        $params = [
            'format' => 'json',
            'api_key' => $uniApiKey,
            'field_names' => $fieldNames,
            'data' => $data,
        ];

        $unisenderApi->importContacts($params);

        return new JsonResponse(['success' => 'true']);
    }
}
