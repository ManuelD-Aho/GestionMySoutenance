<?php

namespace App\Backend\Service\Logger;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use App\Backend\Service\SupervisionAdmin\ServiceSupervisionAdminInterface;

class ServiceLogger implements ServiceLoggerInterface
{
    private Logger $logger;
    private ServiceSupervisionAdminInterface $supervisionService;

    public function __construct(ServiceSupervisionAdminInterface $supervisionService)
    {
        $this->supervisionService = $supervisionService;
        $this->logger = new Logger('GestionMySoutenance');
        $logFilePath = ROOT_PATH . '/var/log/app.log';
        $this->logger->pushHandler(new StreamHandler($logFilePath, Logger::DEBUG));
    }

    public function log(string $level, string $message, array $context = []): void
    {
        $this->logger->{$level}($message, $context);

        if (in_array($level, ['error', 'critical', 'alert', 'emergency'])) {
            $this->supervisionService->enregistrerAction(
                $context['user_id'] ?? 'SYSTEM',
                'ERREUR_APPLICATION',
                $message,
                null,
                null,
                $context
            );
        }
    }

    public function configureErrorHandler(): void
    {
        set_error_handler([$this, 'handleError']);
        set_exception_handler([$this, 'handleException']);
    }

    public function handleError(int $errno, string $errstr, string $errfile, int $errline): bool
    {
        if (!(error_reporting() & $errno)) {
            return false;
        }

        $level = match ($errno) {
            E_ERROR, E_USER_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_RECOVERABLE_ERROR => Logger::CRITICAL,
            E_WARNING, E_USER_WARNING, E_CORE_WARNING, E_COMPILE_WARNING => Logger::WARNING,
            E_PARSE, E_NOTICE, E_USER_NOTICE, E_STRICT, E_DEPRECATED, E_USER_DEPRECATED => Logger::NOTICE,
            default => Logger::INFO,
        };

        $this->log($level, "Erreur PHP: {$errstr}", ['file' => $errfile, 'line' => $errline]);
        return true;
    }

    public function handleException(\Throwable $exception): void
    {
        $this->log(
            Logger::CRITICAL,
            "Exception non capturée: " . $exception->getMessage(),
            [
                'exception' => get_class($exception),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString()
            ]
        );

        if (getenv('APP_ENV') === 'production') {
            http_response_code(500);
            include ROOT_PATH . '/src/Frontend/views/errors/500.php';
        } else {
            http_response_code(500);
            echo "<h1>Erreur 500</h1><p>Détails: " . htmlspecialchars($exception->getMessage()) . "</p>";
            echo "<pre>" . htmlspecialchars($exception->getTraceAsString()) . "</pre>";
        }
    }
}