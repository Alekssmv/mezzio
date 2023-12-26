<?php

declare(strict_types=1);

namespace App\Handler;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Unisender\ApiWrapper\UnisenderApi;
use Exception;

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
        'EMAIL' => 'email',
        'PHONE' => 'phone',
        'POSITION' => 'job_title',
    ];

    /**
     * Обычные поля, которые будут добавлены в элементы массива $contacts перед отправкой в Unisender
     */
    private const FIELDS = [
        'name' => 'Name'
    ];

    /**
     * Коды кастомных полей, которые нужны для отправки в Unisender. Если нет этого поля, то элемент массива $contacts удаляется из массива
     */
    private const REQ_FIELDS = [
        'email' => 'email',
    ];

    private UnisenderApi $unisenderApiClient;
    public function __construct(
        UnisenderApi $unisenderApiClient
    ) {
        $this->unisenderApiClient = $unisenderApiClient;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $unisenderApiClient = $this->unisenderApiClient;
        $params = $request->getQueryParams();

        if (!isset($params['account_id'])) {
            throw new Exception('account_id is not set');
        }
        /**
         * Получаем контакты по собственному маршруту
         */
        $json = file_get_contents($_ENV['NGROK_HOSTNAME'] . '/api/v1/contacts?account_id=' . $params['account_id']);
        $contacts = json_decode($json, true);

        foreach ($contacts as $key => $contact) {

            /**
             * Если нет кастомных полей, то удаляем контакт, т.к email обязательное поле и находится в кастомных полях
             */
            if (empty($contact['custom_fields_values'])) {
                unset($contacts[$key]);
                continue;
            }

            /**
             * Создаем буферный контакт, чтобы не потерять данные
             */
            $bufferContact = $contact;
            unset($contacts[$key]);

            /**
             * Добавляем кастомные поля выбранные по CUSTOM_FIELD_CODES поля в контакт, если они есть и не пустые
             */
            foreach ($bufferContact['custom_fields_values'] as $custom_field) {
                if (isset(self::CUSTOM_FIELD_CODES[$custom_field['field_code']]) && !empty($custom_field['values'][0]['value'])) {
                    $contacts[$key][self::CUSTOM_FIELD_CODES[$custom_field['field_code']]] = $custom_field['values'][0]['value'];
                }
            }

            /**
             * Добавляем обычные поля выбранные по FIELDS поля в контакт, если они есть и не пустые
             */
            foreach (self::FIELDS as $field => $field_code) {
                if (isset($bufferContact[$field]) && !empty($bufferContact[$field])) {
                    $contacts[$key][$field_code] = $bufferContact[$field];
                }
            }

            /**
             * Если нет обязательных полей (REQ_FIELDS), то удаляем контакт
             */
            foreach (self::REQ_FIELDS as $req_field) {
                if (!isset($contacts[$key][$req_field])) {
                    unset($contacts[$key]);
                    continue;
                }
            }
        }

        /**
         * Формируем массив с полями контактов, которые будут отправлены в Unisender
         * Пример переменной $field_names = 
         * [ 0 => "email", 1 => "phone", 2 => "job_title", 3 => "Name"]
         */
        $field_names = array_merge(array_values(self::CUSTOM_FIELD_CODES), array_values(self::FIELDS), );

        /**
         * Форматируем массив с контактами по $field_names для отправки в Unisender 
         * 
         * Было:
         * 
         * 0 => [                                     
         *  "phone" => "+79999999999"
         *  "email" => "vasya@gmail.com" 
         *  "job_title" => "Рабочий"
         *  "Name" => "Вася"
         * ]
         * 
         * Стало:
         * 
         * 0 => [
         *   0 => "vasya@gmail.com"
         *   1 => "+79999999999"
         *   2 => "Рабочий"
         *   3 => "Вася"
         * ]
         */
        $data = [];
        foreach ($contacts as $key => $contact) {
            for ($i = 0; $i < count($field_names); $i++) {
                if ($contact[$field_names[$i]] === null) {
                    continue;
                }
                /**
                 * $key - номер контакта в массиве $contacts, $i - номер поля в массиве $field_names
                 */
                $data[$key][$i] = $contact[$field_names[$i]];
            }
        }

        /**
         * Отправляем контакты в Unisender
         */
        $params = [
            'format' => 'json',
            'api_key' => $_ENV['UNISENDER_API_KEY'],
            'field_names' => $field_names,
            'data' => $data,
        ];
        $unisenderApiClient->importContacts($params);

        return new JsonResponse(['success' => true]);
    }
}
