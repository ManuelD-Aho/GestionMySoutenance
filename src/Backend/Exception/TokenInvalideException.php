<?php

namespace App\Backend\Exception;

class TokenInvalideException extends \InvalidArgumentException
{
    public function __construct(string $message = "Le token fourni est invalide.", int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}