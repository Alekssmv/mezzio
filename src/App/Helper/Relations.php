<?php

declare(strict_types=1);

namespace App\Helper;
use SebastianBergmann\Type\VoidType;


define('RELATIONS_FILE', ROOT_DIR . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'relations.json');

/**
 * Класс для работы с файлом связей между id интеграции и id аккаунта
 */
class Relations
{

    /**
     * Добавляет в файл связь между id интеграции и id аккаунта
     * @param string $clientId
     * @param int $accountId
     * @return void
     */
    static function addRelation(string $clientId, int $accountId): void
    {
        self::createRelationsFile();

        $json = file_get_contents(RELATIONS_FILE);
        $data = json_decode($json, true);

        $data[$clientId] = $accountId;

        file_put_contents(RELATIONS_FILE, json_encode($data));
    }

    /**
     * Возвращает массив со всеми связями
     * @return array|null
     */
    static function getRelations(): array|null
    {
        self::createRelationsFile();

        $relations = file_get_contents(RELATIONS_FILE);
        
        if (!$relations) {
            return null;
        }
        return json_decode($relations, true);
    }

    /**
     * Создает пустой файл, если его нет
     * @return void
     */
    private static function createRelationsFile(): void
    {
        if (!file_exists(RELATIONS_FILE)) {
            file_put_contents(RELATIONS_FILE, '');
        }
    }

    /**
     * Возвращает id аккаунта по id интеграции
     * @param string $clientId
     * @return int|null
     */
    static function getRelation(string $clientId): int|null
    {
        $relations = self::getRelations();
        if (!$relations) {
            return null;
        }
       ;
        if (!isset($relations[$clientId])) {
            return null;
        }
        return $relations[$clientId];
    }

    /**
     * Удаляет все связи
     * @return void
     */
    static function deleteRelations(): void
    {
        file_put_contents(RELATIONS_FILE, '');
    }
}