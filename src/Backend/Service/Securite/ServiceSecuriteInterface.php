<?php
// src/Backend/Service/Securite/ServiceSecuriteInterface.php

namespace App\Backend\Service\Securite;

use App\Backend\Exception\OperationImpossibleException;
use App\Backend\Exception\TokenExpireException;
use App\Backend\Exception\TokenInvalideException;
use App\Backend\Service\Communication\ServiceCommunicationInterface;
use App\Backend\Model\Utilisateur;
interface ServiceSecuriteInterface
{
    //================================================================
    // SECTION 1 : AUTHENTIFICATION & GESTION DE SESSION
    //================================================================
    public function tenterConnexion(string $identifiant, string $motDePasseClair): array;
    public function demarrerSessionUtilisateur(string $numeroUtilisateur): void;
    public function logout(): void;
    public function estUtilisateurConnecte(): bool;
    public function getUtilisateurConnecte(): ?array;

    //================================================================
    // SECTION 2 : GESTION DES MOTS DE PASSE
    //================================================================
    public function demanderReinitialisationMotDePasse(string $emailPrincipal, ServiceCommunicationInterface $communicationService): void;
    public function reinitialiserMotDePasseViaToken(string $tokenClair, string $nouveauMotDePasseClair): bool;
    public function modifierMotDePasse(string $numeroUtilisateur, string $nouveauMotDePasseClair, string $ancienMotDePasseClair): bool;

    //================================================================
    // SECTION 3 : AUTHENTIFICATION À DEUX FACTEURS (2FA)
    //================================================================
    public function genererEtStockerSecret2FA(string $numeroUtilisateur): array;
    public function activerAuthentificationDeuxFacteurs(string $numeroUtilisateur, string $codeTOTP): bool;
    public function desactiverAuthentificationDeuxFacteurs(string $numeroUtilisateur, string $motDePasseClair): bool;
    public function verifierCodeAuthentificationDeuxFacteurs(string $numeroUtilisateur, string $codeTOTP, ?string $secret = null): bool;

    //================================================================
    // SECTION 4 : AUTORISATION & PERMISSIONS
    //================================================================
    public function utilisateurPossedePermission(string $permissionCode, ?string $contexteId = null, ?string $contexteType = null): bool;
    public function synchroniserPermissionsSessionsUtilisateur(string $numeroUtilisateur): void;

    //================================================================
    // SECTION 5 : IMPERSONATION
    //================================================================
    public function demarrerImpersonation(string $adminId, string $targetUserId): bool;
    public function arreterImpersonation(): bool;
    public function estEnModeImpersonation(): bool;
    public function getImpersonatorData(): ?array;

    //================================================================
    // SECTION 6 : GESTION DYNAMIQUE DE L'INTERFACE (NOUVEAU)
    //================================================================
    /**
     * Construit la structure hiérarchique du menu de navigation pour l'utilisateur connecté.
     * Se base sur les permissions de l'utilisateur et les paramètres de visibilité/ordre des menus.
     *
     * @return array La structure du menu prête à être parcourue dans une vue.
     */
    public function construireMenuPourUtilisateurConnecte(): array;
    /**
     * Valide l'adresse email d'un utilisateur via un token.
     *
     * @param string $tokenClair Le token de validation en clair.
     * @return array Les données de l'utilisateur validé.
     * @throws TokenInvalideException Si le token est invalide ou déjà utilisé.
     * @throws TokenExpireException Si le token a expiré.
     * @throws OperationImpossibleException Si l'email est déjà validé.
     */
    public function validateEmailToken(string $tokenClair): array;

    //================================================================
    // SECTION 7 : GESTION DES HABILITATIONS (NOUVEAU)
    //================================================================

    /**
     * Récupère tous les groupes d'utilisateurs.
     * @return array La liste des groupes.
     */
    public function getAllGroupes(): array;

    /**
     * Récupère tous les traitements (permissions) disponibles dans le système.
     * @return array La liste des traitements.
     */
    public function getAllTraitements(): array;

    /**
     * Récupère tous les rattachements actuels entre les groupes et les traitements.
     * @return array La liste des rattachements.
     */
    public function getAllRattachements(): array;

    /**
     * Met à jour l'ensemble des rattachements.
     * Supprime les anciens et insère les nouveaux dans une transaction.
     * @param array $rattachements Tableau associatif [id_groupe => [id_traitement1, id_traitement2]].
     * @return bool True si la mise à jour est réussie.
     */
    public function updateRattachements(array $rattachements): bool;


}