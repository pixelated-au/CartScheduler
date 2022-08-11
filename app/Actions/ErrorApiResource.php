<?php

namespace App\Actions;

use Illuminate\Http\JsonResponse;

class ErrorApiResource extends JsonResponse
{
    public const CODE_NO_AVAILABLE_SHIFTS = 100;

    public function __construct($data = null, $status = 200, $headers = [], $options = 0, $json = false)
    {
        parent::__construct($data, $status, $headers, $options, $json);
    }

    public static function create(string $message, int $errorCode, int $status): self
    {
        return new self(['message' => $message, 'error_code' => $errorCode], $status);
    }
}
