<?php

declare(strict_types=1);

namespace App\Helper;

use League\OAuth2\Client\Token\AccessToken;

/**
 * Класс для работы с API AmoCRM
 */
class AmoApi
{
    /**
     * Возвращает информацию об аккаунте указанного токена в JSON
     */
    static function getAccountInfo(AccessToken $accessToken): string
    {
        $url = 'https://www.amocrm.ru/oauth2/account/subdomain';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt(
            $ch, CURLOPT_HTTPHEADER,
            [
                'Authorization: Bearer ' . $accessToken->getToken(),
            ]
        );
        $response = curl_exec($ch);

        curl_close($ch);

        return $response;
    }
}