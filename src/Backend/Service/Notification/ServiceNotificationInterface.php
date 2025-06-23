<?php
namespace App\Backend\Service\Notification;

interface ServiceNotificationInterface
{
    public function send(string $numeroUtilisateur, string $templateCode, array $variables = []): bool;
    public function sendToGroup(string $idGroupeUtilisateur, string $templateCode, array $variables = []): bool;
    public function getUserNotifications(string $numeroUtilisateur, bool $includeRead = false, int $limit = 20): array;
    public function markAsRead(string $idReception): bool;
    public function countUnread(string $numeroUtilisateur): int;
}