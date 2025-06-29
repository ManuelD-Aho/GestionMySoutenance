<?php

namespace App\Backend\Exception;

/**
 * Exception levée lorsqu'une opération enfreint une contrainte d'unicité (doublon).
 */
class DoublonException extends \RuntimeException
{
    public function __construct(string $message = "Une ressource avec des attributs uniques similaires existe déjà.", int $code = 23000, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}