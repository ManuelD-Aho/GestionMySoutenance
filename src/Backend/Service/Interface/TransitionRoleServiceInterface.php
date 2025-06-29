<?php

declare(strict_types=1);

namespace App\Backend\Service\Interface;

interface TransitionRoleServiceInterface
{
    /**
     * Crée une délégation de droit temporaire d'un utilisateur (délégant) à un autre (délégué).
     *
     * @param string $idDelegant Utilisateur qui délègue.
     * @param string $idDelegue Utilisateur qui reçoit la délégation.
     * @param string $idTraitement Le droit (traitement) spécifique qui est délégué.
     * @param \DateTimeInterface $dateDebut Début de la validité de la délégation.
     * @param \DateTimeInterface $dateFin Fin de la validité de la délégation.
     * @return string L'ID de la délégation créée.
     */
    public function creerDelegation(string $idDelegant, string $idDelegue, string $idTraitement, \DateTimeInterface $dateDebut, \DateTimeInterface $dateFin): string;

    /**
     * Révoque une délégation avant sa date de fin.
     *
     * @param string $idDelegation L'ID de la délégation à révoquer.
     * @return bool
     */
    public function revoquerDelegation(string $idDelegation): bool;

    /**
     * Liste toutes les délégations actuellement actives.
     * Peut être filtré par délégué ou délégant.
     *
     * @param string|null $idDelegue Pour trouver les délégations reçues par cet utilisateur.
     * @param string|null $idDelegant Pour trouver les délégations faites par cet utilisateur.
     * @return array
     */
    public function listerDelegationsActives(?string $idDelegue = null, ?string $idDelegant = null): array;

    /**
     * Récupère la liste des permissions déléguées et actives pour un utilisateur.
     *
     * @param string $numeroUtilisateur L'ID de l'utilisateur.
     * @return array La liste des ID de traitements délégués.
     */
    public function getPermissionsDeleguees(string $numeroUtilisateur): array;

    /**
     * Identifie les tâches (réclamations, rapports à valider, etc.) qui sont encore assignées
     * à un utilisateur sur le point de quitter ses fonctions.
     *
     * @param string $numeroUtilisateurPartant L'ID de l'utilisateur.
     * @return array Tableau de tâches orphelines, chacune avec un 'id_tache' et 'type_tache'.
     */
    public function detecterTachesOrphelines(string $numeroUtilisateurPartant): array;

    /**
     * Réassigne une tâche orpheline à un nouvel utilisateur.
     *
     * @param string $idTache ID de la tâche (ex: ID d'une réclamation).
     * @param string $typeTache Type de la tâche (ex: 'Reclamation').
     * @param string $idNouvelUtilisateur ID du nouvel utilisateur responsable.
     * @return bool
     */
    public function reassignerTache(string $idTache, string $typeTache, string $idNouvelUtilisateur): bool;

    /**
     * Gère l'historique d'un changement de fonction pour un enseignant.
     * Clôture l'ancienne fonction et en ouvre une nouvelle dans la table `occuper`.
     *
     * @param string $numeroEnseignant
     * @param string $idAncienneFonction
     * @param string $idNouvelleFonction
     * @param \DateTimeInterface $dateChangement
     */
    public function historiserChangementDePoste(string $numeroEnseignant, string $idAncienneFonction, string $idNouvelleFonction, \DateTimeInterface $dateChangement): void;
}