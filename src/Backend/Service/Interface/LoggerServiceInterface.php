<?php

declare(strict_types=1);

namespace App\Backend\Service\Interface;

interface LoggerServiceInterface
{
    /**
     * Écrit un message dans le fichier de log approprié (ex: debug, info, warning, error).
     *
     * @param string $level Le niveau de log (selon PSR-3: debug, info, notice, warning, error, critical, alert, emergency).
     * @param string $message Le message à logger.
     * @param array $context Contexte additionnel (ex: données d'une exception).
     */
    public function log(string $level, string $message, array $context = []): void;

    /**
     * Permet d'interroger et de filtrer les fichiers de log.
     *
     * @param array $filtres Critères de filtrage (ex: niveau, date, message contenant...).
     * @return array La liste des entrées de log correspondantes.
     */
    public function queryLogs(array $filtres = []): array;
}