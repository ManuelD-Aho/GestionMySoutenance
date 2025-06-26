<?php

namespace App\Backend\Service\TransitionRole;

interface ServiceTransitionRoleInterface
{
    /**
     * Détecte les tâches actives laissées "orphelines" par un utilisateur.
     * @param string $numeroUtilisateur L'ID de l'utilisateur.
     * @return array Un tableau structuré des tâches orphelines.
     */
    public function detecterTachesOrphelines(string $numeroUtilisateur): array;

    /**
     * Réassigne une tâche orpheline à un nouvel utilisateur.
     * @param string $idTache L'ID de la tâche.
     * @param string $typeTache Le type de la tâche (ex: 'vote', 'validation_pv').
     * @param string $nouveauResponsable L'ID du nouvel utilisateur responsable.
     * @return bool Vrai si la réassignation a réussi.
     */
    public function reassignerTache(string $idTache, string $typeTache, string $nouveauResponsable): bool;

    /**
     * Crée une délégation de responsabilité.
     * @param string $idDelegant L'ID de l'utilisateur qui délègue.
     * @param string $idDelegue L'ID de l'utilisateur qui reçoit la délégation.
     * @param string $idTraitement L'ID du traitement (permission) délégué.
     * @param string $dateDebut La date de début de la délégation.
     * @param string $dateFin La date de fin de la délégation.
     * @param string|null $contexteId L'ID du contexte (ex: ID de session).
     * @param string|null $contexteType Le type du contexte (ex: 'Session').
     * @return string L'ID de la délégation créée.
     */
    public function creerDelegation(string $idDelegant, string $idDelegue, string $idTraitement, string $dateDebut, string $dateFin, ?string $contexteId = null, ?string $contexteType = null): string;

    /**
     * Annule une délégation de manière anticipée.
     * @param string $idDelegation L'ID de la délégation à annuler.
     * @return bool Vrai si l'annulation a réussi.
     */
    public function annulerDelegation(string $idDelegation): bool;
}