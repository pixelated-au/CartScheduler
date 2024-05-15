<?php

namespace App\Actions;

use App\Interfaces\ObfuscatedErrorCode;
use Illuminate\Support\Str;

class EncryptedErrorCodeAction implements ObfuscatedErrorCode
{
    public function __construct(private readonly string $message)
    {
        // $message is injected via teh AppServiceProvider
    }

    public function errorCode(int|string $code): string
    {
        return Str::of(config('app.key'))
            ->pipe(fn(string $key) => sha1($key . $code))
            ->substr(0, 8)
            ->wrap('(code ', ')')
            ->prepend($this->message)
            ->value();
    }
}
