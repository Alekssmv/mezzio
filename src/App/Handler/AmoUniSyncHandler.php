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
use App\Services\EmailService;

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
     * @var EmailService
     */
    private $emailService;

    public function __construct(
        UnisenderApi $unisenderApi,
        ContactFormatterService $contactFormatterService,
        AccountService $accountService,
        AmoCRMApiClient $amoApiClient,
        ContactService $contactService,
        EmailService $emailService
    ) {
        $this->unisenderApi = $unisenderApi;
        $this->contactFormatterService = $contactFormatterService;
        $this->accountService = $accountService;
        $this->amoApiClient = $amoApiClient;
        $this->contactService = $contactService;
        $this->emailService = $emailService;
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
         * @var AccountService $accountService
         */
        $accountService = $this->accountService;

        /**
         * @var ContactService $contactService
         */
        $contactService = $this->contactService;

        /**
         * @var EmailService $emailService
         */
        $emailService = $this->emailService;

        $uniApiKey = null;
        $account = null;
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
        if (empty($params['contacts'])) {
            return new JsonResponse(['error' => 'Contacts are empty'], 400);
        }

        try {
            $account = $this->accountService->findOrCreate((int) $params['account']['id']);
            $uniApiKey = $account->unisender_api_key;
            $accessToken = $account->amo_access_jwt;

            if ($uniApiKey === null) {
                throw new Exception('Unisender api key is not set');
            }
            if ($accessToken === null) {
                throw new Exception('AmoCRM access token is not set');
            }

            /**
             * Устанавливаем accessToken для AmoCRM
             */
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
         * Получаем id enum полей для email
         */
        $emailEnumCodes = json_decode($accountService->findByAccountId((int) $params['account']['id'])->enum_codes);
        $emailEnumCodes = array_flip($emailEnumCodes);
        $customFields = $amoApiClient->customFields('contacts')->get()->toArray();
        $emailField = $emailService->findEmailField($customFields);
        $enumIds = $emailService->findEmailEnumIds($emailField, $emailEnumCodes);

        /**
         * Обрабатываем контакты
         */
        foreach ($params['contacts'] as $action => $contacts) {

            /**
             * Обработка для добавления контактов
             */
            if ($action === 'add') {
                $contacts = $contactFormatterService->formatContacts(
                    $contacts,
                    CUSTOM_FIELD_NAMES,
                    FIELDS,
                    FIELDS_MULTI_VAL,
                    $enumIds
                );
                $contacts = $contactFormatterService->filterContacts($contacts, REQ_FIELDS);
                $contacts = $contactFormatterService->dublicateContacts($contacts, REQ_FIELDS);
                $contactsBuff = array_merge($contactsBuff, $contacts);

                /**
                 * Обработка для обновления контактов
                 */
            } elseif ($action === 'update') {
                $contacts = $contactFormatterService->formatContacts(
                    $contacts,
                    CUSTOM_FIELD_NAMES,
                    FIELDS,
                    FIELDS_MULTI_VAL,
                    $enumIds
                );
                $newEmails = array_column($contacts, 'email', 'id');
                $oldEmails = $contactService->getEmails(array_column($contacts, 'id'));
                $emailsToRemove = ArrayHelper::arrayDiffRecursive($oldEmails, $newEmails);
                $contactsToDel = $contactFormatterService->prepareContactsForDelete($contacts, $emailsToRemove);
                $contacts = $contactFormatterService->dublicateContacts($contacts, REQ_FIELDS);
                $contactsBuff = array_merge($contactsBuff, $contacts, $contactsToDel);

                /**
                 * Обработка для удаления контактов
                 */
            } elseif ($action === 'delete') {
                $ids = array_column($contacts, 'id');
                $emails = $contactService->getEmails($ids);
                $contactsToDel = $contactFormatterService->prepareContactsForDelete($contacts, $emails);
                $contactsBuff = array_merge($contactsBuff, $contactsToDel);
            }
        }
        /**
         * Получаем все поля для Unisender, кроме id
         */
        $fieldNames = $contactFormatterService->getFieldNames(
            CUSTOM_FIELD_NAMES,
            ['name' => 'Name', 'delete' => 'delete'],
            FIELDS_MULTI_VAL
        );
        $data = $contactFormatterService->getDataForUnisender($contactsBuff, $fieldNames);

        $params = [
            'format' => 'json',
            'api_key' => $uniApiKey,
            'field_names' => $fieldNames,
            'data' => $data,
        ];
        $response = $unisenderApi->importContacts($params);
        $response = (json_decode($response)->result);

        /**
         * Получаем индексы контактов, в которых произошла ошибка и пропускаем их
         */
        $indexesToSkip = [];
        if (!empty($response->log)) {
            foreach ($response->log as $val) {
                $indexesToSkip[] = $val->index;
            }
        }
        $indexesToSkip = array_flip($indexesToSkip);

        /**
         * Добавляем и удаляем контакты, в которых не произошло ошибки
         */
        foreach ($contactsBuff as $key => $contact) {
            if (isset($indexesToSkip[$key])) {
                continue;
            } elseif (isset($contact['delete']) && $contact['delete'] == 1) {
                $contactService->deleteEmail($contact['email'], (int) $contact['id']);
            } else {
                $contactService->createOrUpdate(['id' => $contact['id'], 'email' => $contact['email']]);
            }
        }

        return new JsonResponse(['success' => $response], 200);
    }
}
