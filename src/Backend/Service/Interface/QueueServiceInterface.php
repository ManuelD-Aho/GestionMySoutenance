<?php

declare(strict_types=1);

namespace App\Backend\Service\Interface;

use App\Backend\Exception\ElementNonTrouveException;

interface QueueServiceInterface
{
    /**
     * Ajoute une tâche à la file d'attente pour un traitement asynchrone.
     *
     * @param string $nomTache Le nom de la tâche à exécuter (ex: 'SendBulkEmails').
     * @param array $payload Les données nécessaires à l'exécution de la tâche.
     * @param int $priorite La priorité de la tâche.
     * @return bool True si la tâche a été ajoutée.
     */
    public function ajouterTache(string $nomTache, array $payload, int $priorite = 0): bool;

    /**
     * Exécute la prochaine tâche en attente dans la file.
     *
     * @return bool|null True si une tâche a été traitée, false en cas d'échec, null si la file est vide.
     */
    public function traiterProchaineTache(): ?bool;

    /**
     * Liste les tâches de la file par statut.
     *
     * @param string $statut Le statut des tâches à lister ('en attente', 'en cours', 'échouée', 'terminée').
     * @return array La liste des tâches.
     */
    public function listerTaches(string $statut): array;

    /**
     * Vide la file d'attente d'un certain type de tâches (ex: terminées, échouées).
     *
     * @param string $statut Le statut des tâches à purger.
     * @return int Le nombre de tâches supprimées.
     */
    public function purgerFile(string $statut): int;

    /**
     * Tente de ré-exécuter une tâche qui a échoué.
     *
     * @param string $idTache L'ID de la tâche.
     * @return bool True si la tâche a été remise en file.
     * @throws ElementNonTrouveException Si la tâche n'existe pas.
     */
    public function relancerTacheEchouee(string $idTache): bool;

    /**
     * Fournit des statistiques sur l'état de la file d'attente.
     *
     * @return array Statistiques (nombre de tâches par statut, temps d'attente moyen, etc.).
     */
    public function getStatistiquesQueue(): array;
}