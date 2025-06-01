<?php

namespace App\Backend\Model;

use PDO;

class Utilisateur extends BaseModel
{
    protected string $table = 'utilisateur';
    protected string $clePrimaire = 'numero_utilisateur';

    public ?string $numero_utilisateur = null;
    public ?string $login_utilisateur = null;
    public ?string $email_principal = null;
    public ?string $mot_de_passe = null;
    public ?string $date_creation = null;
    public ?string $derniere_connexion = null;
    public ?string $token_reset_mdp = null;
    public ?string $date_expiration_token_reset = null;
    public ?string $token_validation_email = null;
    public ?string $date_expiration_token_validation_email = null;
    public bool $email_valide = false;
    public int $tentatives_connexion_echouees = 0;
    public ?string $compte_bloque_jusqua = null;
    public bool $preferences_2fa_active = false;
    public ?string $secret_2fa = null;
    public ?string $photo_profil = null;
    public string $statut_compte = 'en_attente_validation';
    public ?int $id_niveau_acces_donne = null;
    public ?int $id_groupe_utilisateur = null;
    public ?int $id_type_utilisateur = null;

    public function trouverParNumeroUtilisateur(string $numeroUtilisateur, array $colonnes = ['*']): ?array
    {
        return $this->trouverParIdentifiant($numeroUtilisateur, $colonnes);
    }

    public function trouverParLoginOuEmailPrincipal(string $identifiant, array $colonnes = ['*']): ?array
    {
        $sql = "SELECT " . implode(', ', $colonnes) . " FROM {$this->table} WHERE login_utilisateur = :identifiant OR email_principal = :identifiant LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':identifiant', $identifiant);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public function trouverParLoginUtilisateur(string $login, array $colonnes = ['*']): ?array
    {
        $sql = "SELECT " . implode(', ', $colonnes) . " FROM {$this->table} WHERE login_utilisateur = :login LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':login', $login);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public function trouverParEmailPrincipal(string $email, array $colonnes = ['*']): ?array
    {
        $sql = "SELECT " . implode(', ', $colonnes) . " FROM {$this->table} WHERE email_principal = :email LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public function mettreAJourChamps(string $numeroUtilisateur, array $champsValeurs): bool
    {
        if (empty($champsValeurs)) {
            return false;
        }
        $sets = [];
        foreach (array_keys($champsValeurs) as $champ) {
            $sets[] = "$champ = :$champ";
        }
        $sql = "UPDATE {$this->table} SET " . implode(', ', $sets) . " WHERE {$this->clePrimaire} = :cle_primaire_val";

        $stmt = $this->db->prepare($sql);
        $champsValeurs['cle_primaire_val'] = $numeroUtilisateur;
        return $stmt->execute($champsValeurs);
    }
}