<?php

declare(strict_types=1);

namespace App\Helper;

use League\OAuth2\Client\Token\AccessToken;

define('TOKEN_FOLDER', ROOT_DIR . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'tokens' . DIRECTORY_SEPARATOR);

/**
 * Класс для работы с токенами
 */
class TokenActions
{
    /**
     * Сохраняет токен в TOKEN_FOLDER по $accountId
     * Если переданы не все параметры, то выходит из скрипта
     * @param int $accountId 
     * @param array $accessToken 
     */
    static function saveToken(int $accountId, array $accessToken)
    {
        if (isset($accessToken)
            && isset($accessToken['accessToken'])
            && isset($accessToken['refreshToken'])
            && isset($accessToken['expires'])
            && isset($accessToken['baseDomain'])
        ) {
            $data = [
                'accessToken' => $accessToken['accessToken'],
                'expires' => $accessToken['expires'],
                'refreshToken' => $accessToken['refreshToken'],
                'baseDomain' => $accessToken['baseDomain'],
            ];
            /**
             * Создает директорию, если ее нет
             */
            if (!is_dir(TOKEN_FOLDER)) {
                mkdir(TOKEN_FOLDER, 0777, true);
            }
            file_put_contents(TOKEN_FOLDER . $accountId . '.json', json_encode($data));
        } else {
            exit('Invalid access token ' . var_export($accessToken, true));
        }
    }

    /**
     * Возвращает токен из TOKEN_FOLDER по $accountId
     * Если токен некорректный или его нет, то возвращает Null
     * @param int $accountId
     * @return AccessToken|null
     */
    static function getToken(int $accountId): AccessToken|null
    {
        if (!file_exists(TOKEN_FOLDER . $accountId . '.json')) {
            return null;
        }

        $accessToken = json_decode(file_get_contents(TOKEN_FOLDER . $accountId . '.json'), true);

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
     * Проверяет существование файла TOKEN_FOLDER по $accountId
     * @param int $accountId
     * @return bool
     */
    static function isTokenExist(int $accountId): bool
    {
        if (!file_exists(TOKEN_FOLDER . $accountId . '.json')) {
            return false;
        }
        return true;
    }
}