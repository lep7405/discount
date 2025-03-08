<?php

namespace App\Exceptions;

class DiscountException extends InternalException
{
    //    public static function validateCreate(array $message){
    //        return new self($message);
    //    }
    public static function validateCreate(array $messages): self
    {
        return self::new($messages);
    }

    public static function validateUpdate(array $messages): self
    {
        return self::new($messages);
    }

    public static function notFound(array $messages): self
    {
        return self::new($messages);
    }

    public static function canNotDelete(array $messages): self
    {
        return self::new($messages);
    }

    public static function validateEdit(array $message)
    {
        return new self(null, $message);
    }

    public static function generateExist(array $messages): self
    {
        return self::new($messages);
    }

    public static function discountExpired(array $messages): self
    {
        return self::new($messages);
    }

    public static function inValidStartedAt(array $messages): self
    {
        return self::new($messages);
    }
}
