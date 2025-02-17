<?php

namespace App\Exceptions;

class CouponException extends InternalException
{
    public static function notFound()
    {
        return new self('Coupon not found', 404);
    }

    public static function timesUsedLessThanDecrement()
    {
        return new self('Times used is less than decrement', 400);
    }

    public static function codeAlreadyExist()
    {
        return new self('Code is already exist', 400);
    }

    public static function cannotDelete()
    {
        return new self('Cannot delete coupon', 400);
    }

    public static function inValidArrangeTime()
    {
        return new self('Invalid arrange time', 400);
    }
}
