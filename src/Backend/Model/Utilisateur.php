<?php

namespace App\Backend\Model;

use PDO;
use PDOException; // Ajouté pour une meilleure gestion des exceptions potentielles

class Utilisateur extends BaseModel
{
    protected string $table = 'utilisateur';
    protected string $clePrimaire = 'numero_utilisateur'; // PK est VARCHAR(50)

    // Propriétés pour refléter la structure de la table (optionnel mais peut aider à la clarté)
    // Ces valeurs seraient typiquement remplies lors de la récupération d'un objet utilisateur complet.
    public ?string $numero_utilisateur = null;
    public ?string $login_utilisateur = null;
    public ?string $email_principal = null;
    public ?string $mot_de_passe = null; // Haché
    public ?string $date_creation = null;
    public ?string $derniere_connexion = null;
    public ?string $token_reset_mdp = null;
    public ?string $date_expiration_token_reset = null;
    public ?string $token_validation_email = null;
    // Pas de date_expiration_token_validation_email dans le schéma mysoutenance.sql
    public bool $email_valide = false; // TINYINT(1)
    public int $tentatives_connexion_echouees = 0; // INT UNSIGNED
    public ?string $compte_bloque_jusqua = null;
    public bool $preferences_2fa_active = false; // TINYINT(1)
    public ?string $secret_2fa = null;
    public ?string $photo_profil = null;
    public string $statut_compte = 'en_attente_validation'; // ENUM
    public ?string $id_niveau_acces_donne = null; // VARCHAR(50)
    public ?string $id_groupe_utilisateur = null; // VARCHAR(50)
    public ?string $id_type_utilisateur = null; // VARCHAR(50)

    // La méthode __construct est héritée de BaseModel

    /**
     * Trouve un utilisateur par son numéro unique.
     * Wrapper pour trouverParIdentifiant pour la sémantique.
     */
    public function trouverParNumeroUtilisateur(string $numeroUtilisateur, array $colonnes = ['*']): ?array
    {
        return $this->trouverParIdentifiant($numeroUtilisateur, $colonnes);
    }

    /**
     * Trouve un utilisateur par son login OU son email principal.
     * Utilisé typiquement pour la phase initiale de connexion.
     */
    public function trouverParLoginOuEmailPrincipal(string $identifiant, array $colonnes = ['*']): ?array
    {
        $listeColonnes = implode(', ', array_map(fn($col) => "`$col`", $colonnes));
        $sql = "SELECT {$listeColonnes} FROM `{$this->table}` 
                WHERE `login_utilisateur` = :identifiant OR `email_principal` = :identifiant 
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':identifiant', $identifiant, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Trouve un utilisateur uniquement par son login.
     */
    public function trouverParLoginUtilisateur(string $login, array $colonnes = ['*']): ?array
    {
        $listeColonnes = implode(', ', array_map(fn($col) => "`$col`", $colonnes));
        $sql = "SELECT {$listeColonnes} FROM `{$this->table}` WHERE `login_utilisateur` = :login LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':login', $login, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Trouve un utilisateur uniquement par son email principal.
     */
    public function trouverParEmailPrincipal(string $email, array $colonnes = ['*']): ?array
    {
        $listeColonnes = implode(', ', array_map(fn($col) => "`$col`", $colonnes));
        $sql = "SELECT {$listeColonnes} FROM `{$this->table}` WHERE `email_principal` = :email LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Trouve un utilisateur par son token de réinitialisation de mot de passe.
     * (Le token est stocké haché en BDD, mais cette méthode est un exemple si on le cherchait en clair)
     * Note: Dans ServiceAuthentification, la recherche se fait par token HACHÉ.
     * Si vous voulez une méthode pour chercher par token haché, elle serait :
     * public function trouverParTokenResetMdpHache(string $tokenHache, array $colonnes = ['*']): ?array
     */
    public function trouverParTokenResetMdp(string $tokenClair, array $colonnes = ['*']): ?array
    {
        // Attention: Si le token est stocké haché, cette méthode n'est pas directement utilisable.
        // Il faudrait hacher $tokenClair avant de faire la requête.
        // Cette méthode est plus un exemple conceptuel.
        // La logique de ServiceAuthentification::validerTokenReinitialisationMotDePasse est plus appropriée.
        $tokenHache = hash('sha256', $tokenClair); // Si le token est stocké haché

        $listeColonnes = implode(', ', array_map(fn($col) => "`$col`", $colonnes));
        $sql = "SELECT {$listeColonnes} FROM `{$this->table}` WHERE `token_reset_mdp` = :token_hache LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':token_hache', $tokenHache, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Trouve un utilisateur par son token de validation d'email.
     * (Le token est stocké haché en BDD)
     */
    public function trouverParTokenValidationEmailHache(string $tokenHache, array $colonnes = ['*']): ?array
    {
        $listeColonnes = implode(', ', array_map(fn($col) => "`$col`", $colonnes));
        $sql = "SELECT {$listeColonnes} FROM `{$this->table}` WHERE `token_validation_email` = :token_hache LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':token_hache', $tokenHache, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }


    /**
     * Met à jour des champs spécifiques pour un utilisateur donné par son numero_utilisateur.
     * Utilise la méthode mettreAJourParIdentifiant de BaseModel.
     *
     * @param string $numeroUtilisateur L'ID de l'utilisateur à mettre à jour.
     * @param array $champsValeurs Tableau associatif des champs à mettre à jour et leurs nouvelles valeurs.
     * @return bool True en cas de succès, false sinon.
     */
    public function mettreAJourChamps(string $numeroUtilisateur, array $champsValeurs): bool
    {
        // La clé primaire 'numero_utilisateur' est un VARCHAR, géré correctement par mettreAJourParIdentifiant
        return $this->mettreAJourParIdentifiant($numeroUtilisateur, $champsValeurs);
    }

    /**
     * Vérifie si un login utilisateur existe déjà (utile avant création ou modification).
     *
     * @param string $login Le login à vérifier.
     * @param string|null $numeroUtilisateurExclure Optionnel, pour exclure l'utilisateur actuel lors d'une mise à jour.
     * @return bool True si le login existe, false sinon.
     */
    public function loginExiste(string $login, ?string $numeroUtilisateurExclure = null): bool
    {
        $sql = "SELECT COUNT(*) FROM `{$this->table}` WHERE `login_utilisateur` = :login";
        $params = [':login' => $login];
        if ($numeroUtilisateurExclure !== null) {
            $sql .= " AND `numero_utilisateur` != :numero_exclure";
            $params[':numero_exclure'] = $numeroUtilisateurExclure;
        }
        $stmt = $this->db->prepare($sql);
        // Bind des paramètres
        $stmt->bindParam(':login', $login, PDO::PARAM_STR);
        if ($numeroUtilisateurExclure !== null) {
            $stmt->bindParam(':numero_exclure', $numeroUtilisateurExclure, PDO::PARAM_STR);
        }
        $stmt->execute();
        return (int)$stmt->fetchColumn() > 0;
    }

    /**
     * Vérifie si un email principal existe déjà (utile avant création ou modification).
     *
     * @param string $email L'email à vérifier.
     * @param string|null $numeroUtilisateurExclure Optionnel, pour exclure l'utilisateur actuel lors d'une mise à jour.
     * @return bool True si l'email existe, false sinon.
     */
    public function emailPrincipalExiste(string $email, ?string $numeroUtilisateurExclure = null): bool
    {
        $sql = "SELECT COUNT(*) FROM `{$this->table}` WHERE `email_principal` = :email";
        $params = [':email' => $email];
        if ($numeroUtilisateurExclure !== null) {
            $sql .= " AND `numero_utilisateur` != :numero_exclure";
            $params[':numero_exclure'] = $numeroUtilisateurExclure;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        if ($numeroUtilisateurExclure !== null) {
            $stmt->bindParam(':numero_exclure', $numeroUtilisateurExclure, PDO::PARAM_STR);
        }
        $stmt->execute();
        return (int)$stmt->fetchColumn() > 0;
    }
}