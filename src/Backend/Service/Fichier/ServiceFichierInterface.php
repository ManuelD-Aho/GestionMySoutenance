<?php

namespace App\Backend\Service\Fichier;

interface ServiceFichierInterface
{
    public function getAllFiles(array $filters = [], int $page = 1, int $limit = 20): array;
    public function uploadFile(array $fileData, array $metadata = []): array;
    public function downloadFile(string $id): void;
    public function deleteFile(string $id): bool;
    public function getFileDetails(string $id): ?array;
    public function getFileStats(): array;
    public function getAllowedMimeTypes(): array;
    public function getAllowedExtensions(): array;
    public function getMaxFileSize(): int;
    public function isFileUsed(string $id): bool;
    public function updateFileMetadata(string $id, array $metadata): bool;
    public function getFileUsage(string $id): array;
    public function getFileMetadata(string $id): array;
    public function scanAllFiles(): array;
    public function cleanupOrphanFiles(): array;
}