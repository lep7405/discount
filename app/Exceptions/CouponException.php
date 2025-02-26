<?php

namespace App\Exceptions;

class CouponException extends InternalException
{
    public static function validateCreate(array $messages): self
    {
        return self::new($messages);
    }
    public static function validateCreateByDiscount(array $messages): self
    {
        return self::new($messages);
    }

    public static function notFound(array $messages): self
    {
        return self::new($messages);
    }

    public static function cannotUpdate(array $messages): self
    {
        return self::new($messages);
    }

    public static function timesUsedLessThanDecrement(array $messages): self
    {
        return self::new($messages);
    }

    public static function codeAlreadyExist(array $messages): self
    {
        return self::new($messages);
    }

    public static function cannotDelete(array $messages): self
    {
        return self::new($messages);
    }

    public static function inValidArrangeTime()
    {
        return new self('Invalid arrange time', 400);
    }
}
