<?php

namespace App\Backend\Exception;

class ElementNonTrouveException extends \RuntimeException
{
    public function __construct(string $message = "L'élément demandé n'a pas été trouvé.", int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}