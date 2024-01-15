<?php

namespace Module\Worker;

use AmoCRM\Client\AmoCRMApiClient;
use App\Services\AccountService;
use App\Services\ContactFormatterService;
use App\Services\ContactService;
use App\Services\EmailEnumService;
use Module\Worker\BaseWorker;
use Module\Config\Beanstalk as BeanstalkConfig;
use Exception;
use League\OAuth2\Client\Token\AccessToken;
use App\Helper\ArrayHelper;
use Unisender\ApiWrapper\UnisenderApi;

/**
 * Воркер для обработки задач по выводу времени
 */
class ContactsSync extends BaseWorker
{
    /**
     * @var AccountService - сервис для работы с аккаунтами
     */
    private AccountService $accountService;

    /**
     * @var string $messagesPrefix - префикс для сообщений
     */
    private string $messagesPrefix;

    /**
     * @var AmoCRMApiClient $amoApiClient - клиент для работы с AmoCRM
     */
    private AmoCRMApiClient $amoApiClient;

    /**
     * @var EmailEnumService $emailEnumService - сервис для работы с enum полями email
     */
    private EmailEnumService $emailEnumService;

    /**
     * @var ContactFormatterService $contactFormatterService - сервис для форматирования контактов
     */
    private ContactFormatterService $contactFormatterService;

    /**
     * @var ContactService $contactService - сервис для работы с контактами
     */
    private ContactService $contactService;

    public function __construct(
        BeanstalkConfig $beanstalkConfig,
        string $tube,
        AccountService $accountService,
        AmoCRMApiClient $amoApiClient,
        EmailEnumService $emailEnumService,
        ContactFormatterService $contactFormatterService,
        ContactService $contactService
    ) {
        parent::__construct($beanstalkConfig, $tube);
        $this->accountService = $accountService;
        $this->messagesPrefix = $tube . ': ';
        $this->amoApiClient = $amoApiClient;
        $this->emailEnumService = $emailEnumService;
        $this->contactFormatterService = $contactFormatterService;
        $this->contactService = $contactService;
    }

    /**
     * @var string $data - данные для обработки
     */
    public function process($data): void
    {
        $params = $data;
        $accountService = $this->accountService;
        $messagesPrefix = $this->messagesPrefix;
        $amoApiClient = $this->amoApiClient;
        $emailEnumService = $this->emailEnumService;
        $contactFormatterService = $this->contactFormatterService;
        $contactService = $this->contactService;
        $contactsBuff = [];

        /**
         * Проверяем, что в запросе есть нужные параметры
         */
        if (!isset($params['account']) && !isset($params['contacts'])) {
            echo $messagesPrefix . 'account or contacts are not set' . PHP_EOL;
            return;
        }
        if (empty($params['contacts'])) {
            echo $messagesPrefix . 'contacts is empty' . PHP_EOL;
            return;
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
                    'base_domain' => $json['baseDomain'],
                ]
            );
            $amoApiClient->setAccessToken($accessToken);
            $amoApiClient->setAccountBaseDomain($accessToken->getValues()['base_domain']);

            /**
             * Устанавливаем api key для Unisender
             */
            $unisenderApi = new UnisenderApi($uniApiKey);
        } catch (Exception $e) {
            echo $messagesPrefix . $e->getMessage() . PHP_EOL;
            return;
        }

        /**
         * Получаем id enum полей для email
         */
        $emailEnumCodes = json_decode($accountService->findByAccountId((int) $params['account']['id'])->enum_codes);
        if (empty($emailEnumCodes)) {
            echo $messagesPrefix . 'enum codes is empty' . PHP_EOL;
            return;
        }
        $emailEnumCodes = array_flip($emailEnumCodes);
        $customFields = $amoApiClient->customFields('contacts')->get()->toArray();
        $emailField = $emailEnumService->findEmailField($customFields);
        $enumIds = $emailEnumService->findEmailEnumIds($emailField, $emailEnumCodes);

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
                    $enumIds,
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
                    $enumIds,
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
            [
                'name' => 'Name',
                'delete' => 'delete',
            ],
            FIELDS_MULTI_VAL,
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

        echo $messagesPrefix . 'response: ' . json_encode($response) . PHP_EOL;
        return;
    }

    /**
     * Добавляем воркеру описание
     */
    public function configure(): void
    {
        $this->setDescription('Воркер для синхронизации контактов');
    }
}
