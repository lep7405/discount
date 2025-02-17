<?php

namespace App\Exceptions;

class AuthException extends InternalException
{
    public static function loginFailed()
    {
        return new self('Email or password is incorrect', 401);
    }
}
