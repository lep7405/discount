<?php

namespace App\Exceptions;

use Exception;

class NotFoundException extends Exception
{
    public static function Notfound(string $message)
    {
        return new self($message);
    }
}
