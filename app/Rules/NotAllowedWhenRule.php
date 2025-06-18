<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class NotAllowedWhenRule implements ValidationRule
{
    /**
     * @param \Closure(): bool $condition - The condition to check, must return a boolean
     * @param string  $message - The error message
     */
    public function __construct(
        protected Closure $condition,
        protected string $message
    ) {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (($this->condition)()) {
            $fail($this->message);
        }
    }
}
