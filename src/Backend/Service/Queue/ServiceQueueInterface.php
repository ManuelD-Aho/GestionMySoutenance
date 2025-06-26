<?php

namespace App\Backend\Service\Queue;

interface ServiceQueueInterface
{
    /**
     * Ajoute une tâche à la file d'attente pour un traitement asynchrone.
     * @param string $jobName Le nom de la tâche à exécuter.
     * @param array $payload Les données nécessaires à l'exécution de la tâche.
     * @return bool Vrai si la tâche a été ajoutée avec succès.
     */
    public function ajouterTache(string $jobName, array $payload): bool;

    /**
     * Récupère et traite la prochaine tâche de la file d'attente.
     * @return bool Vrai si une tâche a été traitée, faux sinon.
     */
    public function traiterProchaineTache(): bool;

    /**
     * Liste les tâches en attente ou en cours de traitement.
     * @param array $filters Critères de filtre.
     * @return array Liste des tâches.
     */
    public function listerTachesEnAttente(array $filters = []): array;
}