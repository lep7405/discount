<?php

namespace App\Exceptions;

class DiscountException extends InternalException
{
    public static function notFound()
    {
        return new self('Discount not found', 404);
    }

    public static function canNotDelete()
    {
        return new self('Discount can not deleted', 409);
    }

    public static function validateEdit(string $message)
    {
        return new self("{$message}", 400);
    }

    public static function generateExist()
    {
        return new self('Config Discount already exist', 409);
    }

    public static function discountExpired()
    {
        return new self('Discount is expired', 400);
    }

    public static function inValidStartedAt()
    {
        return new self('Started at not valid', 400);
    }
}
