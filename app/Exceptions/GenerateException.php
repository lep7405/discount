<?php

namespace App\Exceptions;

class GenerateException extends InternalException
{
    public static function notFound()
    {
        return new static('Generate not found');
    }

    public static function validateEdit(string $message)
    {
        return new self("{$message}", 400);
    }
}
