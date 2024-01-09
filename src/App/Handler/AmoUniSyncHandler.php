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

    public function __construct(
        UnisenderApi $unisenderApi,
        ContactFormatterService $contactFormatterService,
        AccountService $accountService,
        AmoCRMApiClient $amoApiClient,
        ContactService $contactService
    ) {
        $this->unisenderApi = $unisenderApi;
        $this->contactFormatterService = $contactFormatterService;
        $this->accountService = $accountService;
        $this->amoApiClient = $amoApiClient;
        $this->contactService = $contactService;
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
         * @var ContactFormatterService $contactFormatterService
         */
        $contactFormatterService = $this->contactFormatterService;

        /**
         * @var ContactService $contactService
         */
        $contactService = $this->contactService;

        $uniApiKey = null;
        $contactsBuff = [];
        $contactsToDel = [];

        /**
         * Получаем контакты из запроса и действия, которое нужно с ними сделать
         */
        $params = $request->getParsedBody();

        /**
         * Проверяем, что в запросе есть нужные параметры
         */
        if (!isset($params['account']) && !isset($params['contacts'])) {
            return new JsonResponse(['error' => 'Invalid request'], 400);
        }

        /**
         * Устанавливаем accessToken для AmoCRM
         */
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

        /**
         * Обрабатываем контакты
         */
        foreach ($params['contacts'] as $action => $contacts) {
            /**
             * Обработка для добавления контактов
             */
            if ($action === 'add') {
                $contacts = $contactFormatterService->formatContacts($contacts, CUSTOM_FIELD_NAMES, FIELDS, FIELDS_MULTI_VAL);
                $contacts = $contactFormatterService->filterContacts($contacts, REQ_FIELDS);
                $contacts = $contactFormatterService->dublicateContacts($contacts, REQ_FIELDS);
                $contactService->createOrUpdateMany($contacts);
                $contactsBuff = array_merge($contactsBuff, $contacts);
            /**
             * Обработка для обновления контактов
             */
            } elseif ($action === 'update') {
                $contacts = $contactFormatterService->formatContacts($contacts, CUSTOM_FIELD_NAMES, FIELDS, FIELDS_MULTI_VAL);
                $contacts = $contactFormatterService->filterContacts($contacts, REQ_FIELDS);

                $newEmails = array_column($contacts, 'email', 'id');
                $oldEmails = $contactService->getEmails(array_column($contacts, 'id'));
                $emailsToRemove = ArrayHelper::arrayDiffRecursive($oldEmails, $newEmails);
                
                $contactsToDel = $contactFormatterService->prepareContactsForDelete($contacts, $emailsToRemove);
                $contactService->deleteEmails($emailsToRemove);
                $contacts = $contactFormatterService->dublicateContacts($contacts, REQ_FIELDS);
                $contactService->createOrUpdateMany($contacts);
                $contactsBuff = array_merge($contactsBuff, $contacts, $contactsToDel);
            /**
             * Обработка для удаления контактов
             */
            } elseif ($action === 'delete') {
                $ids = array_column($contacts, 'id');
                $emails = $contactService->getEmails($ids);
                $contactsToDel = $contactFormatterService->prepareContactsForDelete($contacts, $emails);
                $contactService->deleteMany($ids);
                $contactsBuff = array_merge($contactsBuff, $contactsToDel);
            }
        }

        /**
         * Удаляем из массива контактов id, т.к. он не нужен для импорта в Unisender
         */
        $contactsBuff = $contactFormatterService->removeFieldsFromContacts($contactsBuff, ['id']);

        $fieldNames = $contactFormatterService->getFieldNames(CUSTOM_FIELD_NAMES, FIELDS, FIELDS_MULTI_VAL);
        $data = $contactFormatterService->getDataForUnisender($contactsBuff, $fieldNames);
        $params = [
            'format' => 'json',
            'api_key' => $uniApiKey,
            'field_names' => $fieldNames,
            'data' => $data,
        ];

        $response = $unisenderApi->importContacts($params);

        return new JsonResponse(['success' => $response], 200);
    }
}
