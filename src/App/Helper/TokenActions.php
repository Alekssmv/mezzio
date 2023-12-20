<?php

declare(strict_types=1);

namespace App\Helper;

use League\OAuth2\Client\Token\AccessToken;

define('TOKEN_FILE', ROOT_DIR . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'token.json');

/**
 * Класс для работы с токенами
 */
class TokenActions
{
    /**
     * Сохраняет токен в TOKEN_FILE по $accountId
     * Если переданы не все параметры, то выходит из скрипта
     * @param int $accountId 
     * @param array $accessToken 
     */
    static function saveToken(array|int $accountId, array $accessToken)
    {
        if (is_string($accountId)) {
            dd('test');
            $accountId = (int)$accountId;
        }

        $data = self::getTokens();
        if (isset($accessToken)
            && isset($accessToken['accessToken'])
            && isset($accessToken['refreshToken'])
            && isset($accessToken['expires'])
            && isset($accessToken['baseDomain'])
        ) {
            $data[$accountId] = $accessToken;

            file_put_contents(TOKEN_FILE, json_encode($data));
        } else {
            exit('Invalid access token ' . var_export($accessToken, true));
        }
    }

    /**
     * Возвращает токен из TOKEN_FILE по $accountId
     * Если токен некорректный или его нет, то возвращает Null
     * @param int $accountId
     * @return AccessToken|null
     */
    static function getToken(int|string $accountId): AccessToken|null
    {
        /*
        * Если передана строка, то преобразуем ее в число
        */
        if (is_string($accountId)) {
            $accountId = (int)$accountId;
        }

        if (!file_exists(TOKEN_FILE)) {
            return null;
        }

        $data = json_decode(file_get_contents(TOKEN_FILE), true);
        $accessToken = $data[$accountId] ?? null;

        if (isset($accessToken)
            && isset($accessToken['accessToken'])
            && isset($accessToken['refreshToken'])
            && isset($accessToken['expires'])
            && isset($accessToken['baseDomain'])
        ) {
            return new AccessToken(
                [
                    'access_token' => $accessToken['accessToken'],
                    'refresh_token' => $accessToken['refreshToken'],
                    'expires' => $accessToken['expires'],
                    'baseDomain' => $accessToken['baseDomain'],
                ]
            );
        } else {
            return null;
        }
    }
    /**
     * Проверяет существование файла TOKEN_FILE по $accountId
     * @param int $accountId
     * @return bool
     */
    static function isTokenExist(int $accountId): bool
    {
        if (!file_exists(TOKEN_FILE)) {
            return false;
        }

        $data = json_decode(file_get_contents(TOKEN_FILE), true);
        $accessToken = $data[$accountId] ?? null;

        if (isset($accessToken)
            && isset($accessToken['accessToken'])
            && isset($accessToken['refreshToken'])
            && isset($accessToken['expires'])
            && isset($accessToken['baseDomain'])
        ) {
            return true;
        } else {
            return false;
        }
    }
    /**
     * Возвращает все токены из TOKEN_FILE
     * @return array
     */
    static function getTokens(): array
    {
        if (!file_exists(TOKEN_FILE)) {
            return [];
        }

        $data = json_decode(file_get_contents(TOKEN_FILE), true);

        return $data;
    }
}
