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

    public static function canNotDelete(): self
    {
        return self::new(['error' => 'Can not delete discount']);
    }

    public static function discountExpired(): self
    {
        return self::new(['error' => 'Discount expired']);
    }

    public static function inValidStartedAt(): self
    {
        return self::new(['error' => 'Invalid started_at']);
    }

    public static function restrictUpdateFieldsForUsedDiscount(): self
    {
        return self::new(['error' => 'Cannot update type, value, trial_days, discount_for_x_month after discount is used.']);
    }
}
