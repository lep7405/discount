<?php

namespace App\Exceptions;

class GenerateException extends InternalException
{
    public static function notFound(array $messages): self
    {
        return self::new($messages);
    }
    public static function generateExist(array $messages): self
    {
        return self::new($messages);
    }

    public static function validateEdit(string $message)
    {
        return new self("{$message}", 400);
    }

    public static function validateCreate(array $messages): self
    {
        return self::new($messages);

    }

    public static function validateUpdate(array $messages): self
    {
        return self::new($messages);
    }
}
