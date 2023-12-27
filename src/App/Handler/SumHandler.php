<?php

declare(strict_types=1);

namespace App\Handler;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class SumHandler implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $a = (int) $request->getAttribute('a');
        $b = (int) $request->getAttribute('b');
        $sum = $a + $b;
        return new JsonResponse(['sum' => $sum]);
    }
}
