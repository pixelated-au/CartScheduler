<?php

namespace Tests\Unit\Exceptions;

use App\Exceptions\EndpointUpdatedException;
use Tests\TestCase;

class EndpointUpdatedExceptionTest extends TestCase
{

    public function test_throw(): void
    {
        $this->assertThrows(
            fn() => EndpointUpdatedException::throw('A test message'),
            EndpointUpdatedException::class,
            'A test message');
    }

    public function test_wrap(): void
    {
        $this->expectException(EndpointUpdatedException::class);
        $this->expectExceptionMessage('A test message');

        $exception = EndpointUpdatedException::wrap('A test message');
        $this->assertIsCallable($exception);
        $exception();
    }

    public function test_create(): void
    {
        $this->assertThrows(
            fn() => throw EndpointUpdatedException::create('A test message'),
            EndpointUpdatedException::class,
            'A test message');
    }
}
