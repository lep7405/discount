<?php

namespace App\Exceptions;

class AuthException extends InternalException
{
    //    public static function loginFailed()
    //    {
    //        return new self(, 401);
    //    }
    public static function loginFailed(): self
    {
        return self::new(['error' => 'Email or password is incorrect']);
    }
    public static function validateLogin(array $messages):self
    {
        return self::new($messages);
    }
    public static function validateRegister(array $messages):self
    {
        return self::new($messages);
    }
    public static function validateChangePassWord(array $messages):self
    {
        return self::new($messages);
    }
}
