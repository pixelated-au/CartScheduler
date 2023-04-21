<?php

namespace App\Exceptions;

use App\Actions\ErrorApiResource;
use RuntimeException;

class VolunteerIsAllowedException extends RuntimeException
{
    private int $exceptionType;

    public static function brotherRequired(): self
    {
        return (new self('Sorry, the last volunteer for this shift needs to be a brother'))
            ->setExceptionType(ErrorApiResource::CODE_BROTHER_REQUIRED);
    }

    public function getExceptionType(): string
    {
        return $this->exceptionType;
    }

    public function setExceptionType(int $exceptionType): self
    {
        $this->exceptionType = $exceptionType;
        return $this;
    }
}
