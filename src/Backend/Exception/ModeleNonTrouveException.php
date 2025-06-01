<?php

namespace App\Backend\Exception;

class ModeleNonTrouveException extends \InvalidArgumentException
{
    public function __construct(string $message = "Le modèle demandé n'a pas été trouvé.", int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}