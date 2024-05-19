<?php

namespace App\Exceptions;

use Closure;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class EndpointUpdatedException extends HttpException
{

    public static function wrap(string $message): Closure
    {
        return static fn() => self::throw($message);
    }

    /**
     * @throws \App\Exceptions\EndpointUpdatedException
     */
    public static function throw(string $message): void
    {
        throw self::create($message);
    }

    public static function create(string $message): self
    {
        return new self(Response::HTTP_BAD_REQUEST, "ENDPOINT UPDATED: $message");
    }
}
