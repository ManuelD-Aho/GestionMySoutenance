<?php

namespace App\Backend\Model;

use PDO;
use App\Backend\Exception\DoublonException; // Assurez-vous d'importer cette exception

class Utilisateur extends BaseModel
{
    protected string $table = 'utilisateur';
    protected string|array $primaryKey = 'numero_utilisateur'; // Clé primaire de type string

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }

    /**
     * Prépare la liste des colonnes pour une requête SQL.
     * Surcharge la méthode de BaseModel si des logiques spécifiques sont nécessaires,
     * sinon elle pourrait être retirée et utiliser celle de BaseModel directement.
     * @param array $colonnes Les noms des colonnes à sélectionner.
     * @return string La chaîne des colonnes formatée.
     */
    protected function preparerListeColonnes(array $colonnes): string
    {
        return parent::preparerListeColonnes($colonnes); // Appelle la méthode du parent
    }

    /**
     * Trouve un utilisateur par son numéro unique.
     * @param string $numeroUtilisateur Le numéro unique de l'utilisateur.
     * @param array $colonnes Les colonnes à sélectionner.
     * @return array|null Les données de l'utilisateur ou null si non trouvé.
     */
    public function trouverParNumeroUtilisateur(string $numeroUtilisateur, array $colonnes = ['*']): ?array
    {
        return $this->trouverUnParCritere(['numero_utilisateur' => $numeroUtilisateur], $colonnes);
    }

    /**
     * Trouve un utilisateur par son login ou son email principal.
     * @param string $identifiant Le login ou l'email principal de l'utilisateur.
     * @param array $colonnes Les colonnes à sélectionner.
     * @return array|null Les données de l'utilisateur ou null si non trouvé.
     */
    public function trouverParLoginOuEmailPrincipal(string $identifiant, array $colonnes = ['*']): ?array
    {
        return $this->trouverUnParCritere([
            'login_utilisateur' => $identifiant,
            'email_principal' => $identifiant
        ], $colonnes, 'OR'); // Recherche par login OU email
    }

    /**
     * Trouve un utilisateur par son login.
     * @param string $login Le login de l'utilisateur.
     * @param array $colonnes Les colonnes à sélectionner.
     * @return array|null Les données de l'utilisateur ou null si non trouvé.
     */
    public function trouverParLoginUtilisateur(string $login, array $colonnes = ['*']): ?array
    {
        return $this->trouverUnParCritere(['login_utilisateur' => $login], $colonnes);
    }

    /**
     * Trouve un utilisateur par son email principal.
     * @param string $email L'email principal de l'utilisateur.
     * @param array $colonnes Les colonnes à sélectionner.
     * @return array|null Les données de l'utilisateur ou null si non trouvé.
     */
    public function trouverParEmailPrincipal(string $email, array $colonnes = ['*']): ?array
    {
        return $this->trouverUnParCritere(['email_principal' => $email], $colonnes);
    }

    /**
     * Trouve un utilisateur par un token de réinitialisation de mot de passe (haché).
     * @param string $tokenHache Le token de réinitialisation de mot de passe haché.
     * @param array $colonnes Les colonnes à sélectionner.
     * @return array|null Les données de l'utilisateur ou null si non trouvé.
     */
    public function trouverParTokenResetMdp(string $tokenHache, array $colonnes = ['*']): ?array
    {
        return $this->trouverUnParCritere(['token_reset_mdp' => $tokenHache], $colonnes);
    }

    /**
     * Trouve un utilisateur par un token de validation d'email (haché).
     * @param string $tokenHache Le token de validation d'email haché.
     * @param array $colonnes Les colonnes à sélectionner.
     * @return array|null Les données de l'utilisateur ou null si non trouvé.
     */
    public function trouverParTokenValidationEmailHache(string $tokenHache, array $colonnes = ['*']): ?array
    {
        return $this->trouverUnParCritere(['token_validation_email' => $tokenHache], $colonnes);
    }

    /**
     * Met à jour des champs spécifiques d'un utilisateur par son numéro unique.
     * @param string $numeroUtilisateur Le numéro de l'utilisateur à mettre à jour.
     * @param array $champsValeurs Un tableau associatif des colonnes et de leurs nouvelles valeurs.
     * @return bool Vrai si la mise à jour a réussi, faux sinon.
     * @throws DoublonException Si la mise à jour provoque une violation de contrainte d'unicité (ex: login/email déjà pris).
     */
    public function mettreAJourChamps(string $numeroUtilisateur, array $champsValeurs): bool
    {
        // CORRECTION : Utiliser la méthode de BaseModel conçue pour les clés primaires simples.
        // C'est plus sûr et sémantiquement correct.
        return $this->mettreAJourParIdentifiant($numeroUtilisateur, $champsValeurs);
    }

    /**
     * Vérifie si un login existe déjà pour un autre utilisateur.
     * @param string $login Le login à vérifier.
     * @param string|null $numeroUtilisateurExclure Le numéro de l'utilisateur à exclure de la vérification (pour les mises à jour).
     * @return bool Vrai si le login existe déjà pour un autre utilisateur, faux sinon.
     */
    public function loginExiste(string $login, ?string $numeroUtilisateurExclure = null): bool
    {
        $criteres = ['login_utilisateur' => $login];
        if ($numeroUtilisateurExclure !== null) {
            $criteres['numero_utilisateur'] = ['operator' => '!=', 'value' => $numeroUtilisateurExclure];
        }
        return $this->compterParCritere($criteres) > 0;
    }

    /**
     * Vérifie si un email principal existe déjà pour un autre utilisateur.
     * @param string $email L'email à vérifier.
     * @param string|null $numeroUtilisateurExclure Le numéro de l'utilisateur à exclure de la vérification.
     * @return bool Vrai si l'email existe déjà pour un autre utilisateur, faux sinon.
     */
    public function emailPrincipalExiste(string $email, ?string $numeroUtilisateurExclure = null): bool
    {
        $criteres = ['email_principal' => $email];
        if ($numeroUtilisateurExclure !== null) {
            $criteres['numero_utilisateur'] = ['operator' => '!=', 'value' => $numeroUtilisateurExclure];
        }
        return $this->compterParCritere($criteres) > 0;
    }
}