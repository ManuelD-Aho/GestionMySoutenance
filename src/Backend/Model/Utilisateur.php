<?php

namespace App\Backend\Model;

use PDO;
use PDOException;

class Utilisateur extends BaseModel
{
    protected string $table = 'utilisateur';
    protected string $clePrimaire = 'numero_utilisateur';

    // Déclaration des propriétés
    public ?string $numero_utilisateur = null;
    public ?string $login_utilisateur = null;
    public ?string $email_principal = null;
    public ?string $mot_de_passe = null;
    public ?string $date_creation = null;
    public ?string $derniere_connexion = null;
    public ?string $token_reset_mdp = null;
    public ?string $date_expiration_token_reset = null;
    public ?string $token_validation_email = null;
    public bool $email_valide = false;
    public int $tentatives_connexion_echouees = 0;
    public ?string $compte_bloque_jusqua = null;
    public bool $preferences_2fa_active = false;
    public ?string $secret_2fa = null;
    public ?string $photo_profil = null;
    public string $statut_compte = 'en_attente_validation';
    public ?string $id_niveau_acces_donne = null;
    public ?string $id_groupe_utilisateur = null;
    public ?string $id_type_utilisateur = null;

    // Cette méthode devrait être dans BaseModel.php pour éviter la duplication.
    // Assurez-vous que la version dans BaseModel est aussi corrigée.
    protected function preparerListeColonnes(array $colonnes): string
    {
        if (count($colonnes) === 1 && $colonnes[0] === '*') {
            return '*';
        }
        return implode(', ', array_map(fn($col) => "`" . trim(str_replace('`', '', $col)) . "`", $colonnes));
    }

    public function trouverParNumeroUtilisateur(string $numeroUtilisateur, array $colonnes = ['*']): ?array
    {
        return $this->trouverParIdentifiant($numeroUtilisateur, $colonnes);
    }

    public function trouverParLoginOuEmailPrincipal(string $identifiant, array $colonnes = ['*']): ?array
    {
        $listeColonnes = $this->preparerListeColonnes($colonnes);
        // ALTERNATIVE: Utilisation de placeholders positionnels pour déboguer
        $sql = "SELECT {$listeColonnes} FROM `{$this->table}` 
                WHERE `login_utilisateur` = ? OR `email_principal` = ? 
                LIMIT 1";
        try {
            $stmt = $this->db->prepare($sql);
            // Lier les deux placeholders avec la même valeur d'$identifiant
            $stmt->execute([$identifiant, $identifiant]); // <-- Ligne 59 (ou proche)
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (PDOException $e) {
            error_log("Erreur PDO dans trouverParLoginOuEmailPrincipal: " . $e->getMessage() . " SQL: " . $sql . " Identifiant: " . $identifiant);
            throw $e;
        }
    }

    public function trouverParLoginUtilisateur(string $login, array $colonnes = ['*']): ?array
    {
        $listeColonnes = $this->preparerListeColonnes($colonnes);
        $sql = "SELECT {$listeColonnes} FROM `{$this->table}` WHERE `login_utilisateur` = :login LIMIT 1";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':login' => $login]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (PDOException $e) {
            error_log("Erreur PDO dans trouverParLoginUtilisateur: " . $e->getMessage() . " SQL: " . $sql);
            throw $e;
        }
    }

    public function trouverParEmailPrincipal(string $email, array $colonnes = ['*']): ?array
    {
        $listeColonnes = $this->preparerListeColonnes($colonnes);
        $sql = "SELECT {$listeColonnes} FROM `{$this->table}` WHERE `email_principal` = :email LIMIT 1";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':email' => $email]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (PDOException $e) {
            error_log("Erreur PDO dans trouverParEmailPrincipal: " . $e->getMessage() . " SQL: " . $sql);
            throw $e;
        }
    }

    public function trouverParTokenResetMdp(string $tokenClair, array $colonnes = ['*']): ?array
    {
        $tokenHache = hash('sha256', $tokenClair);
        $listeColonnes = $this->preparerListeColonnes($colonnes);
        $sql = "SELECT {$listeColonnes} FROM `{$this->table}` WHERE `token_reset_mdp` = :token_hache LIMIT 1";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':token_hache' => $tokenHache]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (PDOException $e) {
            error_log("Erreur PDO dans trouverParTokenResetMdp: " . $e->getMessage() . " SQL: " . $sql);
            throw $e;
        }
    }

    public function trouverParTokenValidationEmailHache(string $tokenHache, array $colonnes = ['*']): ?array
    {
        $listeColonnes = $this->preparerListeColonnes($colonnes);
        $sql = "SELECT {$listeColonnes} FROM `{$this->table}` WHERE `token_validation_email` = :token_hache LIMIT 1";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':token_hache' => $tokenHache]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (PDOException $e) {
            error_log("Erreur PDO dans trouverParTokenValidationEmailHache: " . $e->getMessage() . " SQL: " . $sql);
            throw $e;
        }
    }

    public function mettreAJourChamps(string $numeroUtilisateur, array $champsValeurs): bool
    {
        return $this->mettreAJourParIdentifiant($numeroUtilisateur, $champsValeurs);
    }

    public function loginExiste(string $login, ?string $numeroUtilisateurExclure = null): bool
    {
        $sql = "SELECT COUNT(*) FROM `{$this->table}` WHERE `login_utilisateur` = :login";
        $params = [':login' => $login];
        if ($numeroUtilisateurExclure !== null) {
            $sql .= " AND `numero_utilisateur` != :numero_exclure";
            $params[':numero_exclure'] = $numeroUtilisateurExclure;
        }
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return (int)$stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Erreur PDO dans loginExiste: " . $e->getMessage() . " SQL: " . $sql);
            throw $e;
        }
    }

    public function emailPrincipalExiste(string $email, ?string $numeroUtilisateurExclure = null): bool
    {
        $sql = "SELECT COUNT(*) FROM `{$this->table}` WHERE `email_principal` = :email";
        $params = [':email' => $email];
        if ($numeroUtilisateurExclure !== null) {
            $sql .= " AND `numero_utilisateur` != :numero_exclure";
            $params[':numero_exclure'] = $numeroUtilisateurExclure;
        }
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return (int)$stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Erreur PDO dans emailPrincipalExiste: " . $e->getMessage() . " SQL: " . $sql);
            throw $e;
        }
    }
}
