<?php

declare(strict_types=1);

namespace App\Backend\Service;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;
use App\Backend\Service\Interface\LoggerServiceInterface;

class ServiceLogger implements LoggerServiceInterface
{
    private Logger $logger;
    private string $logFilePath;

    public function __construct()
    {
        $this->logFilePath = __DIR__ . '/../../../var/log/application.log';
        $this->logger = new Logger('GestionMySoutenance');

        $formatter = new LineFormatter(
            "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n",
            "Y-m-d H:i:s",
            true,
            true
        );

        $handler = new StreamHandler($this->logFilePath, Logger::DEBUG);
        $handler->setFormatter($formatter);

        $this->logger->pushHandler($handler);
    }

    public function log(string $level, string $message, array $context = []): void
    {
        if (!in_array(strtolower($level), Logger::getLevels())) {
            $level = 'info';
        }
        $this->logger->{strtolower($level)}($message, $context);
    }

    public function queryLogs(array $filtres = []): array
    {
        if (!file_exists($this->logFilePath) || !is_readable($this->logFilePath)) {
            return [];
        }

        $lines = file($this->logFilePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $results = [];

        foreach (array_reverse($lines) as $line) {
            if (isset($filtres['level']) && !preg_match('/\.'.strtoupper($filtres['level']).':/', $line)) {
                continue;
            }
            if (isset($filtres['message']) && stripos($line, $filtres['message']) === false) {
                continue;
            }
            if (isset($filtres['date_from'])) {
                preg_match('/\[(.*?)\]/', $line, $matches);
                if (isset($matches[1]) && new \DateTime($matches[1]) < new \DateTime($filtres['date_from'])) {
                    continue;
                }
            }

            $results[] = $line;

            if (isset($filtres['limit']) && count($results) >= $filtres['limit']) {
                break;
            }
        }

        return $results;
    }
}