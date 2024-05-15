<?php

namespace App\Interfaces;

interface ObfuscatedErrorCode
{
    public function errorCode(int|string $code): string;
}
