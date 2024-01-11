<?php

declare(strict_types=1);

namespace App\Services;

use Exception;

/**
 * Сервис для работы с email custom field
 */
class EmailService
{
    /**
     * Возвращает email custom field
     * @param array $customFields - кастомные поля, полученные из amoCRM
     * @return array
     * @throws Exception
     */
    public function findEmailField(array $customFields): array
    {
        foreach ($customFields as $customField) {
            if ($customField['name'] === 'Email') {
                return $customField;
            }
        }
        throw new Exception('Email field not found');
    }

    /**
     * Возвращает массив id enum-ов email по enum-кодам
     * @param array $customField - кастомное поле email
     * @param array $enumEmailCodes - массив enum-кодов email, в формате ['WORK' => 0, enum-код2 => 0]
     */
    public function findEmailEnumIds(array $customField, array $enumEmailCodes): array
    {
        dd($enumEmailCodes);
        $enums = $customField['enums'];
        $enumIds = [];
        foreach ($enums as $enum) {
            if (isset($enumEmailCodes[$enum['value']])) {
                $enumIds['Email'][$enum['id']] = 0;
            }
        }
        return $enumIds;
    }
}
