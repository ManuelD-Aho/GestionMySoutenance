<?php

declare(strict_types=1);

namespace App\Backend\Service\Interface;

use App\Backend\Exception\DoublonException;
use App\Backend\Exception\ElementNonTrouveException;
use App\Backend\Exception\MotDePasseInvalideException;
use App\Backend\Exception\OperationImpossibleException;
use App\Backend\Exception\TokenInvalideException;

interface CompteUtilisateurServiceInterface
{
    /**
     * Crée le compte technique de connexion d'un utilisateur.
     *
     * @param array $donneesLogin Données du compte (login, email, mot_de_passe).
     * @param string $idGroupe L'ID du groupe d'appartenance.
     * @param string $idType L'ID du type d'utilisateur.
     * @param string $idNiveauAcces L'ID du niveau d'accès aux données.
     * @return string L'ID du nouveau compte utilisateur.
     * @throws DoublonException Si le login ou l'email existe déjà.
     */
    public function creerCompte(array $donneesLogin, string $idGroupe, string $idType, string $idNiveauAcces): string;

    /**
     * Met à jour les informations de base du compte (login, email).
     *
     * @param string $numeroUtilisateur L'ID de l'utilisateur.
     * @param array $donnees Les données à mettre à jour.
     * @return bool True en cas de succès.
     * @throws ElementNonTrouveException Si l'utilisateur n'existe pas.
     * @throws DoublonException Si le nouveau login/email est déjà pris.
     */
    public function mettreAJourInformationsBase(string $numeroUtilisateur, array $donnees): bool;

    /**
     * Modifie le statut d'un compte (actif, bloqué, archivé).
     *
     * @param string $numeroUtilisateur L'ID de l'utilisateur.
     * @param string $nouveauStatut Le nouveau statut à appliquer.
     * @return bool True en cas de succès.
     */
    public function changerStatut(string $numeroUtilisateur, string $nouveauStatut): bool;

    /**
     * Permet à un utilisateur connecté de changer son propre mot de passe.
     *
     * @param string $numeroUtilisateur L'ID de l'utilisateur.
     * @param string $ancienMdp L'ancien mot de passe pour vérification.
     * @param string $nouveauMdp Le nouveau mot de passe.
     * @return bool True en cas de succès.
     * @throws MotDePasseInvalideException Si l'ancien mot de passe est incorrect ou si le nouveau ne respecte pas les règles.
     */
    public function changerMotDePasse(string $numeroUtilisateur, string $ancienMdp, string $nouveauMdp): bool;

    /**
     * Permet à un administrateur de forcer la réinitialisation du mot de passe d'un utilisateur.
     *
     * @param string $numeroUtilisateur L'ID de l'utilisateur.
     * @return string Le nouveau mot de passe temporaire (si applicable) ou un message de succès.
     */
    public function reinitialiserMotDePasseParAdmin(string $numeroUtilisateur): string;

    /**
     * Génère un secret pour l'authentification à deux facteurs (2FA).
     *
     * @param string $numeroUtilisateur L'ID de l'utilisateur.
     * @return array Contenant le secret et l'URL du QR code.
     */
    public function genererSecret2FA(string $numeroUtilisateur): array;

    /**
     * Active la 2FA pour un utilisateur après vérification du premier code.
     *
     * @param string $numeroUtilisateur L'ID de l'utilisateur.
     * @param string $code Le code 2FA pour valider l'activation.
     * @return bool True en cas de succès.
     */
    public function activer2FA(string $numeroUtilisateur, string $code): bool;

    /**
     * Désactive la 2FA pour un compte utilisateur.
     *
     * @param string $numeroUtilisateur L'ID de l'utilisateur.
     * @return bool True en cas de succès.
     */
    public function desactiver2FA(string $numeroUtilisateur): bool;

    /**
     * Lie un compte technique à son profil métier (étudiant, enseignant).
     *
     * @param string $numeroUtilisateur L'ID du compte utilisateur.
     * @param string $idEntite L'ID du profil métier (ex: numero_carte_etudiant).
     * @param string $typeEntite Le type de profil ('etudiant', 'enseignant', 'personnel').
     * @return bool True en cas de succès.
     */
    public function lierEntiteMetier(string $numeroUtilisateur, string $idEntite, string $typeEntite): bool;

    /**
     * Valide l'adresse email d'un compte en utilisant un token.
     *
     * @param string $token Le token de validation reçu par email.
     * @return bool True en cas de succès.
     * @throws TokenInvalideException Si le token est invalide ou a expiré.
     */
    public function validerEmail(string $token): bool;

    /**
     * Envoie ou renvoie l'email de validation à l'utilisateur.
     *
     * @param string $numeroUtilisateur L'ID de l'utilisateur.
     * @return bool True si l'email a été envoyé.
     */
    public function envoyerEmailDeValidation(string $numeroUtilisateur): bool;

    /**
     * Supprime un compte utilisateur (opération sensible avec vérifications).
     *
     * @param string $numeroUtilisateur L'ID de l'utilisateur.
     * @return bool True en cas de succès.
     * @throws OperationImpossibleException Si le compte ne peut être supprimé (ex: données liées).
     */
    public function supprimerCompte(string $numeroUtilisateur): bool;

    /**
     * Liste les comptes utilisateurs avec des filtres techniques.
     *
     * @param array $filtres Critères de filtrage (ex: groupe, statut).
     * @return array La liste des comptes.
     */
    public function listerComptes(array $filtres = []): array;
}