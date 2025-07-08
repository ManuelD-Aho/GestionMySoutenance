<?php

namespace App\Backend\Service\Logger;

interface ServiceLoggerInterface
{
    public function getLogFiles(): array;
    public function getLogStats(): array;
    public function getRecentErrors(int $limit): array;
    public function getLogContent(string $file, array $filters = [], int $page = 1, int $limit = 100): array;
    public function getLogFileInfo(string $file): array;
    public function clearLogFile(string $file): bool;
    public function downloadLogFile(string $file): void;
    public function getLogFileSize(string $file): string;
    public function archiveOldLogs(int $retentionDays): array;
    public function analyzeLogFile(string $file): array;
    public function getLogConfiguration(): array;
    public function updateLogConfiguration(array $config): bool;
    public function getLogModules(): array;
    public function exportLogs(array $criteria): void;
}