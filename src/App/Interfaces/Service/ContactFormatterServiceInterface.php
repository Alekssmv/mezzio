<?php

declare(strict_types=1);

namespace App\Interfaces\Service;

/**
 * Интерфейс для сервиса, для работы с контактами
 */
interface ContactFormatterServiceInterface
{
    public function formatContacts(array $contacts, array $customFieldCodes, array $fields, array $fieldsMultiVal, array $enumIds): array;

    public function filterContacts(array $contacts, array $reqFields): array;

    public function dublicateContacts(array $contacts, array $fields): array;

    public function getDataForUnisender(array $contacts, array $fieldNames): array;

    public function getFieldNames(array $fields, array $customFieldNames, array $fieldsMultiVal): array;

    public function addFieldsToContacts(array $contacts, array $data): array;

    public function removeFieldsFromContacts(array $contacts, array $fields): array;

    public function prepareContactsForDelete(array $contacts, array $emails): array;
}
