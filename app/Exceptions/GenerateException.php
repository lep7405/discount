<?php

namespace App\Exceptions;

class GenerateException extends InternalException
{
    public static function generateExist(): self
    {
        return self::new(['error' => 'Generate existed discount_id and app_name']);
    }


    public static function validateCreate(array $messages): self
    {
        return self::new($messages);

    }

    public static function validateUpdate(array $messages): self
    {
        return self::new($messages);
    }

    public static function canNotUpdateDiscountIdAndAppName(): self
    {
        return self::new(['error' => 'Can not update discount id and app name']);
    }
}
