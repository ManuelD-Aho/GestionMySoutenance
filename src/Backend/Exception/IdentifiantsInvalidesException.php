<?php

namespace App\Backend\Exception;

class IdentifiantsInvalidesException extends AuthenticationException
{
    public function __construct(string $message = "Identifiants invalides.", int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}