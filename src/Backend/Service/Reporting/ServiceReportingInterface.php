<?php

namespace App\Backend\Service\Reporting;

interface ServiceReportingInterface
{
    public function getAvailableReports(): array;
    public function generateReport(string $type, array $parameters): array;
    public function exportReport(string $type, string $format, array $report = null): void;
    public function getRecentReports(int $limit = 10): array;
    public function getReportingStats(): array;
    public function getScheduledReports(): array;
    public function scheduleReport(array $data): string;
    public function deleteScheduledReport(string $id): bool;
}