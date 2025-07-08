<?php

namespace App\Backend\Service\Queue;

interface ServiceQueueInterface
{
    public function getQueueStatus(): array;
    public function getQueueStats(): array;
    public function getRecentJobs(int $limit): array;
    public function getJobTypes(): array;
    public function processQueue(int $limit): array;
    public function clearQueue(string $type): array;
    public function getJobDetails(string $id): ?array;
    public function getJobLogs(string $id): array;
    public function retryJob(string $id): string;
    public function cancelJob(string $id): bool;
    public function addJob(array $jobData): string;
    public function getConfiguration(): array;
    public function updateConfiguration(array $config): bool;
    public function getAvailableJobTypes(): array;
}