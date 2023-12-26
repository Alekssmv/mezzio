<?php

declare(strict_types=1);

namespace App\Handler;

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
     * Коды кастомных полей, которые будут добавлены в элементы массива $contacts перед отправкой в Unisender.
     * Ключ - код кастомного поля. Значение - ключ, который добавится в элемент $contacts.
     */
    private const CUSTOM_FIELD_CODES = [
        'PHONE' => 'phone',
        'POSITION' => 'job_title',
    ];

    /**
     * Обычные поля, которые будут добавлены в элементы массива $contacts перед отправкой в Unisender
     */
    private const FIELDS = [
        'name' => 'Name',
        'email' => 'email'
    ];

    /**
     * Обязательные поля, которые должны быть в элементах массива $contacts перед отправкой в Unisender
     */
    private const REQ_FIELDS = [
        'email' => 'email'
    ];

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
        ContactsService $contactsService
    ) {
        $this->unisenderApiClient = $unisenderApiClient;
        $this->contactsService = $contactsService;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $unisenderApiClient = $this->unisenderApiClient;
        $params = $request->getQueryParams();

        if (!isset($params['account_id'])) {
            return new JsonResponse(['error' => 'account_id is required']);
        }

        $this->contactsService->setToken((int) $params['account_id']);
        $contacts = $this->contactsService->getContacts();
        
        $contacts = $this->contactsService->formatContacts($contacts, self::CUSTOM_FIELD_CODES, self::FIELDS);
        $contacts = $this->contactsService->filterContacts($contacts, self::REQ_FIELDS);
        $contacts = $this->contactsService->dublicateContacts($contacts, self::REQ_FIELDS);
        $fieldNames = array_merge(array_values(self::CUSTOM_FIELD_CODES), array_values(self::FIELDS));
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
