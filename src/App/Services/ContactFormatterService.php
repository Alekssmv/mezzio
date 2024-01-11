<?php

declare(strict_types=1);

namespace App\Services;

use App\Interfaces\Service\ContactFormatterServiceInterface;

/**
 * Сервис, для работы с контактами
 */
class ContactFormatterService implements ContactFormatterServiceInterface
{
    /**
     * Форматируем контакты
     * @param array $contacts - контакты из amoCRM
     * @param array $fields - поля, которые нужно добавить в элементы $contacts
     * Ключ - название поля (в контакте из amoCrm), значение - название поля, которое добавится в элементы $contacts
     * Пример: ['PHONE' => 'phone', 'POSITION' => 'job_title',]
     * @param array $customFieldNames - кастомные поля, которые нужно добавить в элементы $contacts
     * Ключ - имя кастомного поля, значение - название поля, которое добавится в элементы $contacts
     * Пример: ['name' => 'Name',]
     * @param array $fieldsMultiVal - поля, которые нужно добавить в элементы $contacts и которые могут иметь множество значений
     * Ключ - имя поля (field_name). 'enum_code' - признак по которому будут добавляться поля. 'name' - поле, которое добавится в элемент $contacts
     * Пример: [
     *  'Email' => [
     *      'enum_code' => 'WORK',
     *      'name' => 'email'
     *      ]
     *  ];
     * @param array $enumIds - массив с id enum полей, по которым нужно добавить поля с множественными значениями
     * @return array - отформатированные контакты
     */
    public function formatContacts(
        array $contacts,
        array $customFieldNames,
        array $fields,
        array $fieldsMultiVal,
        array $enumIds
    ): array {
        foreach ($contacts as $key => $contact) {

            /**
             * Создаем буферный контакт, чтобы не потерять данные
             */
            $bufferContact = $contact;
            unset($contacts[$key]);

            $customFieldsKey = 'custom_fields_values';
            if (!isset($bufferContact[$customFieldsKey])) {
                $customFieldsKey = 'custom_fields';
            }

            $customFieldName = 'field_name';
            if (!isset($bufferContact[$customFieldsKey][0][$customFieldName])) {
                $customFieldName = 'name';
            }

            if ($bufferContact[$customFieldsKey] !== null) {
                /**
                 * Добавляем кастомные поля выбранные по $customFieldNames поля в контакт, если они есть и не пустые
                 */
                foreach ($bufferContact[$customFieldsKey] as $customField) {
                    if (
                        isset($customFieldNames[$customField[$customFieldName]]) &&
                        !empty($customField['values'][0]['value'])
                    ) {
                        $contacts[$key][$customFieldNames[$customField[$customFieldName]]] = $customField['values'][0]['value'];
                    }
                }

                /*
                 * Добавляем поля с множественными значениями выбранные по $fieldsMultiVal поля в контакт
                 */
                foreach ($fieldsMultiVal as $fieldKey => $fieldValue) {
                    foreach ($bufferContact[$customFieldsKey] as $customField) {
                        if (
                            $customField[$customFieldName] === $fieldKey &&
                            isset($customField['values'])
                        ) {
                            foreach ($customField['values'] as $value) {
                                if (isset($enumIds[$fieldKey][$value['enum']])) {
                                    $contacts[$key][$fieldValue][] = $value['value'];
                                }
                            }
                        }
                    }
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
        }
        return $contacts;
    }

    /**
     * Принимает форматированные методом formatContacts контакты
     * Фильтруем контакты
     * @param array $contacts - форматированные контакты
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
     * @param array $contacts - форматированные контакты
     * @param array $fields - поля, по которым нужно дублировать контакты
     * @return array
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
     * @param array $contacts - форматированные контакты
     * @param array $fieldNames - поля по которым будет произведено форматирование
     * @return array - data для отправки в unisender
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

    /**
     * Возвращает параметр field_names для отправки в Unisender
     * @param array $fields
     * @param array $customFieldNames
     * @param array $fieldsMultiVal
     * @return array
     */
    public function getFieldNames(array $fields, array $customFieldNames, array $fieldsMultiVal): array
    {
        return array_merge(array_values($customFieldNames), array_values($fields), array_values($fieldsMultiVal));
    }

    /**
     * Принимает массив отформатированных контактов
     * Добавляет данные из массива $data в массив $contacts
     * @param array $contacts - отформатированные контакты
     * @param array $data - данные, которые нужно добавить в контакты
     * @return array - контакты с добавленными данными
     */
    public function addFieldsToContacts(array $contacts, array $data): array
    {
        foreach ($contacts as $key => $contact) {
            foreach ($data as $dataKey => $dataValue) {
                $contacts[$key][$dataKey] = $dataValue;
            }
        }
        return $contacts;
    }

    /**
     * Удаляет поля из форматированных контактов
     */
    public function removeFieldsFromContacts(array $contacts, array $fields): array
    {
        foreach ($contacts as $key => $contact) {
            foreach ($fields as $field) {
                unset($contacts[$key][$field]);
            }
        }
        return $contacts;
    }

    /**
     * Возвращает массив с контактами, которые нужно удалить
     */
    public function prepareContactsForDelete(array $contacts, array $emails): array
    {
        $contactsToDel = [];
        foreach ($emails as $id => $emails) {
            foreach ($emails as $email) {
                foreach ($contacts as $contact) {
                    if ((int) $contact['id'] === $id) {
                        $contactsToDel[] = [
                            'id' => $contact['id'],
                            'email' => $email,
                            'delete' => 1,
                        ];
                    }
                }
            }
        }

        return $contactsToDel;
    }
}
