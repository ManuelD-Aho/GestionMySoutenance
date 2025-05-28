<?php

namespace App\Backend\Exception;

class DoublonException extends \RuntimeException
{
    public function __construct(string $message = "Une ressource avec des attributs uniques similaires existe déjà.", int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}