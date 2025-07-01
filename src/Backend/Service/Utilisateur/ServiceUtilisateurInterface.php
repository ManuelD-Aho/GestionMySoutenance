<?php
// src/Backend/Service/Utilisateur/ServiceUtilisateurInterface.php

namespace App\Backend\Service\Utilisateur;

interface ServiceUtilisateurInterface
{
    /**
     * Crée une entité métier (Etudiant, Enseignant...) sans créer de compte utilisateur.
     *
     * @param string $typeEntite 'etudiant', 'enseignant', ou 'personnel'.
     * @param array $donneesProfil Les données du profil (nom, prénom, etc.).
     * @return string Le numéro unique de l'entité créée (ex: 'ETU-2024-0125').
     */
    public function creerEntite(string $typeEntite, array $donneesProfil): string;

    /**
     * Active l'accès à la plateforme pour une entité existante en créant son compte utilisateur.
     *
     * @param string $numeroEntite L'ID de l'entité (ex: numero_carte_etudiant).
     * @param array $donneesCompte Données pour le compte (login, email, groupe, etc.).
     * @param bool $envoyerEmailValidation Indique s'il faut envoyer un email de validation.
     * @return bool True si le compte a été activé avec succès.
     */
    public function activerComptePourEntite(string $numeroEntite, array $donneesCompte, bool $envoyerEmailValidation = true): bool;

    /**
     * Récupère la liste complète des utilisateurs avec leurs profils et statuts de compte.
     *
     * @param array $filtres Critères de filtrage.
     * @return array Liste des utilisateurs.
     */
    public function listerUtilisateursComplets(array $filtres = []): array;

    /**
     * Met à jour les informations d'un profil et/ou du compte utilisateur associé.
     *
     * @param string $numeroUtilisateur L'ID de l'utilisateur.
     * @param array $donneesProfil Données du profil à mettre à jour.
     * @param array $donneesCompte Données du compte à mettre à jour.
     * @return bool True en cas de succès.
     */
    public function mettreAJourUtilisateur(string $numeroUtilisateur, array $donneesProfil, array $donneesCompte): bool;

    /**
     * Change le statut d'un compte utilisateur (actif, inactif, bloqué, archivé).
     *
     * @param string $numeroUtilisateur L'ID de l'utilisateur.
     * @param string $nouveauStatut Le nouveau statut.
     * @return bool True en cas de succès.
     */
    public function changerStatutCompte(string $numeroUtilisateur, string $nouveauStatut): bool;

    /**
     * Crée une délégation de permission d'un utilisateur à un autre.
     *
     * @param string $idDelegant L'ID de l'utilisateur qui délègue.
     * @param string $idDelegue L'ID de l'utilisateur qui reçoit la délégation.
     * @param string $idTraitement L'ID de la permission déléguée.
     * @param string $dateDebut Date de début de la délégation.
     * @param string $dateFin Date de fin de la délégation.
     * @param string|null $contexteId Contexte spécifique de la délégation (optionnel).
     * @param string|null $contexteType Type de contexte (optionnel).
     * @return string L'ID de la délégation créée.
     */
    public function creerDelegation(string $idDelegant, string $idDelegue, string $idTraitement, string $dateDebut, string $dateFin, ?string $contexteId = null, ?string $contexteType = null): string;

    /**
     * Révoque une délégation avant sa date de fin.
     *
     * @param string $idDelegation L'ID de la délégation à révoquer.
     * @return bool True en cas de succès.
     */
    public function revoquerDelegation(string $idDelegation): bool;
    /**
     * Récupère les permissions déléguées d'un utilisateur.
     *
     * @param string $numeroUtilisateur L'ID de l'utilisateur.
     * @return array Liste des permissions déléguées.
     */
/**
 * Importe des entités étudiant en masse depuis un fichier (CSV ou Excel).
 * Ne crée pas les comptes utilisateurs, seulement les profils.
 *
 * @param string $filePath Le chemin absolu vers le fichier temporaire uploadé.
 * @param array $mapping Un tableau associatif qui mappe les colonnes du fichier aux champs de la DB.
 *                      Ex: ['Nom de famille' => 'nom', 'Prénom' => 'prenom', 'Email U' => 'email_contact_secondaire']
 * @return array Un rapport d'importation avec le nombre de succès et les erreurs détaillées.
 */
public function importerEtudiantsDepuisFichier(string $filePath, array $mapping): array;
}