<?php

declare(strict_types=1);

namespace App\Handler;

use App\Services\UserService;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;

/*
 * Обработчик для создания пользователя, принимает имя пользователя (name) в параметрах запроса
 * Возвращает созданного пользователя
 */
class CreateUserHandler implements RequestHandlerInterface
{
    /**
     * @var UserService - сервис для работы с таблицей бд users
     */
    private UserService $userService;

    public function __construct(
        UserService $userService
    ) {
        $this->userService = $userService;
    }
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $params = $request->getQueryParams();
        if (!isset($params['name'])) {
            return new JsonResponse(['error' => 'name is required']);
        }
        $name = $params['name'];
        $user = $this->userService->create($name);
        return new JsonResponse(['user' => $user]);
    }
}
