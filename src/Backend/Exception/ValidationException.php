<?php

namespace App\Backend\Exception;

class ValidationException extends \InvalidArgumentException
{
    private array $errors;

    public function __construct(string $message = "Erreur de validation des données.", array $errors = [], int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->errors = $errors;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}