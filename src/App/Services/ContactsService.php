<?php

declare(strict_types=1);

namespace App\Services;

use AmoCRM\Client\AmoCRMApiClient;
use App\Helper\TokenActions;
use App\Interfaces\ContactsServiceInterface;

/**
 * Сервис, для работы с контактами
 */
class ContactsService implements ContactsServiceInterface
{
    /**
     * Клиент для работы с API amoCRM
     */
    private AmoCRMApiClient $apiClient;
    public function __construct(
        AmoCRMApiClient $apiClient
    ) {
        $this->apiClient = $apiClient;
    }
    /**
     * Устанавливаем токен для этого сервиса
     */
    public function setToken(int $accountId): void
    {
        $accessToken = TokenActions::getToken($accountId);
        $baseDomain = $accessToken->getValues()['baseDomain'];
        $this->apiClient->setAccessToken($accessToken)->setAccountBaseDomain($baseDomain);
    }

    /**
     * Получаем контакты из amoCRM
     * @return array
     */
    public function getContacts(): array
    {
        $contacts = $this->apiClient->contacts()->get()->toArray();
        return $contacts;
    }

    /**
     * Форматируем контакты
     * @param array $contacts - контакты из amoCRM
     * @param array $fields - поля, которые нужно добавить в элементы $contacts
     * Ключ - название поля (в контакте из amoCrm), значение - название поля, которое добавится в элементы $contacts
     * Пример: ['PHONE' => 'phone', 'POSITION' => 'job_title',]
     * @param array $customFieldCodes - кастомные поля, которые нужно добавить в элементы $contacts
     * Ключ - код кастомного поля, значение - название поля, которое добавится в элементы $contacts
     * Пример: ['name' => 'Name',]
     * @param array $fieldsMultiVal - поля, которые нужно добавить в элементы $contacts и которые могут иметь множество значений
     * Ключ и значение - название поля, которое добавится в элементы $contacts
     * Пример: ['email' => 'email',]
     * @return array - отформатированные контакты
     */
    public function formatContacts(array $contacts, array $customFieldCodes, array $fields, array $fieldsMultiVal): array
    {
        foreach ($contacts as $key => $contact) {

            /**
             * Создаем буферный контакт, чтобы не потерять данные
             */
            $bufferContact = $contact;
            unset($contacts[$key]);

            /**
             * Добавляем кастомные поля выбранные по $customFieldCodes поля в контакт, если они есть и не пустые
             */
            foreach ($bufferContact['custom_fields_values'] as $custom_field) {
                if (isset($customFieldCodes[$custom_field['field_code']]) && !empty($custom_field['values'][0]['value'])) {
                    $contacts[$key][$customFieldCodes[$custom_field['field_code']]] = $custom_field['values'][0]['value'];
                }
            }

            /**
             * Добавляем обычные поля выбранные по $fields поля в контакт, если они есть и не пустые
             */
            foreach ($fields as $fieldKey => $fieldValue) {
                if (isset($bufferContact[$fieldKey]) && !empty($bufferContact[$fieldKey])) {
                    $contacts[$key][$fieldValue] = $bufferContact[$fieldKey];
                }
            }

            if (isset($fieldsMultiVal['email'])) {
                /**
                 * Добавляем множество email'ов в контакт
                 */
                foreach ($bufferContact['custom_fields_values'] as $custom_field) {
                    if (filter_var($custom_field['values'][0]['value'], FILTER_VALIDATE_EMAIL)) {
                        $contacts[$key]['email'][] = $custom_field['values'][0]['value'];
                    }
                }
            }
        }
        return $contacts;
    }

    /**
     * Принимает форматированные методом formatContacts контакты
     * Фильтруем контакты
     * @param array $contacts - контакты из amoCRM
     * @param array $reqFields - обязательные поля
     * @return array - отфильтрованные контакты
     */
    public function filterContacts(array $contacts, array $reqFields): array
    {
        foreach ($contacts as $key => $contact) {
            /**
             * Если нет обязательных полей (REQ_FIELDS), то удаляем контакт
             */
            foreach ($reqFields as $req_field) {
                if (!isset($contacts[$key][$req_field])) {
                    unset($contacts[$key]);
                    continue;
                }
            }
        }
        return $contacts;
    }

    /**
     * Принимает форматированные методом formatContacts контакты
     * Дублируем контакты с многочисленными значениями выбранных полей
     * @param array $contacts - контакты из amoCRM
     * @param array $fields - поля, по которым нужно дублировать контакты
     */
    public function dublicateContacts(array $contacts, array $fields): array
    {
        $dublicateContacts = [];
        foreach ($contacts as $key => $contact) {
            foreach ($fields as $field) {
                if (isset($contact[$field])) {
                    foreach ($contact[$field] as $value) {
                        $bufferContact = $contact;
                        $bufferContact[$field] = $value;
                        $dublicateContacts[] = $bufferContact;
                    }
                }
            }
        }

        return $dublicateContacts;
    }

    /**
     * Принимает форматированные методом formatContacts контакты
     * Форматируем массив с контактами по $field_names для отправки в Unisender
     *
     * Пример переменной $field_names =
     * [ 0 => "email", 1 => "phone", 2 => "job_title", 3 => "Name"]
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
    public function getDataForUnisender(array $contacts, array $fieldNames): array
    {
        $data = [];
        foreach ($contacts as $key => $contact) {
            for ($i = 0; $i < count($fieldNames); $i++) {
                if ($contact[$fieldNames[$i]] === null) {
                    continue;
                }
                /**
                 * $key - номер контакта в массиве $contacts, $i - номер поля в массиве $fieldNames
                 */
                $data[$key][$i] = $contact[$fieldNames[$i]];
            }
        }
        return $data;
    }
}
