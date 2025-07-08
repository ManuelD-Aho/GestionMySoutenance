<?php

namespace App\Backend\Service\Delegation;

interface ServiceDelegationInterface
{
    public function countActiveDelegations(): int;
    public function countExpiredDelegations(): int;
    public function countOrphanTasks(): int;
    public function countAbsentUsers(): int;
    public function getRecentDelegations(int $limit): array;
    public function getAllDelegations(array $filters = []): array;
    public function getDelegation(string $id): ?array;
    public function createDelegation(array $data): string;
    public function updateDelegation(string $id, array $data): bool;
    public function deleteDelegation(string $id): bool;
    public function activateDelegation(string $id): bool;
    public function deactivateDelegation(string $id): bool;
    public function detectOrphanTasks(string $userId): array;
    public function reassignTask(string $taskId, string $newAssigneeId, string $reason): bool;
    /**
     * Révoque une délégation en changeant son statut.
     * @param string $idDelegation L'ID de la délégation à révoquer.
     * @return bool True si la révocation est réussie.
     */
    public function revoquerDelegation(string $idDelegation): bool;
}