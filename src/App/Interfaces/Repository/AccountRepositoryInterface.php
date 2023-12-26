<?php

namespace App\Interfaces\Repository;


/**
 * Интерфейс для репозитория аккаунтов
 */
interface AccountRepositoryInterface
{
    /**
     * Принимает массив данных для создания аккаунта,
     * ключ - название поля в таблице, значение - значение поля
     * @param array $data
     * @return void
     */
    public function create(array $data);
}
