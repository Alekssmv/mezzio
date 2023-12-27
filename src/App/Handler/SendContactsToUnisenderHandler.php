<?php

declare(strict_types=1);

namespace App\Handler;

use AmoCRM\Client\AmoCRMApiClient;
use App\Helper\TokenActions;
use App\Services\ContactsService;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Unisender\ApiWrapper\UnisenderApi;

/**
 * Маршрут для отправки контактов из amoCrm в Unisender
 */
class SendContactsToUnisenderHandler implements RequestHandlerInterface
{
    /**
     * Имена кастомных полей, которые будут добавлены в элементы массива $contacts перед отправкой в Unisender.
     * Ключ - имя кастомного поля. Значение - ключ, который добавится в элемент $contacts.
     */
    private const CUSTOM_FIELD_NAMES = [
        'Телефон' => 'phone',
        'Должность' => 'job_title',
        'Рабочий email' => 'email'
    ];

    /**
     * Обычные поля, которые будут добавлены в элементы массива $contacts перед отправкой в Unisender
     * Ключ - имя поля. Значение - ключ, который добавится в элемент $contacts.
     */
    private const FIELDS = [
        'name' => 'Name',
    ];

    /**
     * Поля которые будут содержать множество значений
     * Ключ - имя кастомного поля. Значение - enum_code, по которому будут добавляться значения
     */
    private const FIELDS_MULTI_VAL = [
        'Email' => 'WORK',
    ];

    /**
     * Обязательные поля, которые должны быть в элементах массива $contacts перед отправкой в Unisender
     */
    private const REQ_FIELDS = [
        'email' => 'email'
    ];

    private AmoCRMApiClient $apiClient;

    /**
     * Клиент для работы с Unisender API
     */
    private UnisenderApi $unisenderApiClient;

    /**
     * Сервис для работы с контактами
     */
    private ContactsService $contactsService;
    public function __construct(
        UnisenderApi $unisenderApiClient,
        AmoCRMApiClient $amoCRMApiClient,
        ContactsService $contactsService
    ) {
        $this->unisenderApiClient = $unisenderApiClient;
        $this->contactsService = $contactsService;
        $this->apiClient = $amoCRMApiClient;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $unisenderApiClient = $this->unisenderApiClient;
        $params = $request->getQueryParams();

        if (!isset($params['account_id'])) {
            return new JsonResponse(['error' => 'account_id is required']);
        }

        $token = TokenActions::getToken((int) $params['account_id']);
        $baseDomain = $token->getValues()['baseDomain'];
        $this->apiClient->setAccessToken($token)->setAccountBaseDomain($baseDomain);
        $contacts = $this->apiClient->contacts()->get()->toArray();
        $contacts = $this->contactsService->formatContacts($contacts, self::CUSTOM_FIELD_NAMES, self::FIELDS, self::FIELDS_MULTI_VAL);
        $contacts = $this->contactsService->filterContacts($contacts, self::REQ_FIELDS);
        $contacts = $this->contactsService->dublicateContacts($contacts, self::REQ_FIELDS);
        $fieldNames = array_merge(array_values(self::CUSTOM_FIELD_NAMES), array_values(self::FIELDS));
        $data = $this->contactsService->getDataForUnisender($contacts, $fieldNames);

        $params = [
            'format' => 'json',
            'api_key' => $_ENV['UNISENDER_API_KEY'],
            'field_names' => $fieldNames,
            'data' => $data,
        ];
        $unisenderApiClient->importContacts($params);

        return new JsonResponse(['success' => true]);
    }
}
