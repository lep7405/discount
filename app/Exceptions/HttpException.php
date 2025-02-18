<?php

namespace App\Exceptions;

class HttpException extends InternalException
{
    public static function connectionError(string $message = 'Connection Error!')
    {
        return new static($message);
    }
}
