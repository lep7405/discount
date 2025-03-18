<?php

namespace App\Exceptions;

class AuthException extends InternalException
{
    public static function loginFailed(array $message): self
    {
        return self::new($message);
    }

    public static function validateLogin(array $messages): self
    {
        return self::new($messages);
    }

    public static function validateRegister(array $messages): self
    {
        return self::new($messages);
    }

    public static function validateChangePassWord(array $messages): self
    {
        return self::new($messages);
    }
}
