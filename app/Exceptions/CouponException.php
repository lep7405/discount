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


    public static function cannotUpdate(): self
    {
        return self::new(['error' => 'Coupon can not update']);
    }

    public static function timesUsedLessThanDecrement(): self
    {
        return self::new(['error' => ['Invalid numDecrement']]);
    }

    public static function codeAlreadyExist(): self
    {
        return self::new(['error' => 'Code existed']);
    }
    public static function cannotDeleteCouponAlreadyUsed()
    {
        return self::new(['error' => 'Can not delete coupon already used']);
    }
}
