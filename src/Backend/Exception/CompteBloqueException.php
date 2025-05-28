<?php

namespace App\Backend\Exception;

class CompteBloqueException extends AuthenticationException
{
    public function __construct(string $message = "Le compte est bloqué.", int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}