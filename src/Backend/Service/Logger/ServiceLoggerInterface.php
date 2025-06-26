<?php

namespace App\Backend\Service\Logger;

interface ServiceLoggerInterface
{
    /**
     * Log un message avec un niveau de sévérité donné.
     * @param string $level Le niveau de log (ex: 'info', 'warning', 'error').
     * @param string $message Le message à logger.
     * @param array $context Contexte additionnel.
     */
    public function log(string $level, string $message, array $context = []): void;

    /**
     * Configure les gestionnaires d'erreurs et d'exceptions de PHP pour utiliser ce service.
     */
    public function configureErrorHandler(): void;
}