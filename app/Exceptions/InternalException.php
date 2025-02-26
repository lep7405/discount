<?php

namespace App\Exceptions;

use Exception;

class InternalException extends Exception
{
    protected array $errors;

    public static function new(?array $errors = []): static
    {
        $exception = new static;
        $exception->errors = $errors;

        return $exception;
    }

    public function getErrors(): array
    {
        return $this->errors ?? [];
    }
}
