<?php
// src/Backend/Model/Utilisateur.php

namespace App\Backend\Model;

use PDO;

class Utilisateur extends BaseModel
{
    // --- CORRECTION ICI : Changer protected en public ---
    public string $table = 'utilisateur';
    // --- FIN DE LA CORRECTION ---
    protected string|array $primaryKey = 'numero_utilisateur';
    protected array $fields = [
        'numero_utilisateur', 'login_utilisateur', 'email_principal', 'mot_de_passe', 'date_creation',
        'derniere_connexion', 'token_reset_mdp', 'date_expiration_token_reset', 'token_validation_email',
        'email_valide', 'tentatives_connexion_echouees', 'compte_bloque_jusqua', 'preferences_2fa_active',
        'secret_2fa', 'photo_profil', 'statut_compte', 'id_niveau_acces_donne', 'id_groupe_utilisateur', 'id_type_utilisateur'
    ];

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }

    /**
     * Trouve un utilisateur par son login ou son email principal.
     * Utilisé pour la première étape de l'authentification.
     *
     * @param string $identifiant Le login ou l'email à rechercher.
     * @return array|null Les données de l'utilisateur ou null si non trouvé.
     */
    public function trouverParLoginOuEmailPrincipal(string $identifiant): ?array
    {
        $sql = "SELECT * FROM `{$this->table}` WHERE `login_utilisateur` = :identifiant OR `email_principal` = :identifiant LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':identifiant', $identifiant);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Trouve un utilisateur par son token de réinitialisation de mot de passe (haché).
     *
     * @param string $tokenHache Le token haché à rechercher.
     * @return array|null Les données de l'utilisateur ou null si non trouvé.
     */
    public function trouverParTokenResetMdp(string $tokenHache): ?array
    {
        return $this->trouverUnParCritere(['token_reset_mdp' => $tokenHache]);
    }

    /**
     * Trouve un utilisateur par son token de validation d'email (haché).
     *
     * @param string $tokenHache Le token haché à rechercher.
     * @return array|null Les données de l'utilisateur ou null si non trouvé.
     */
    public function trouverParTokenValidationEmail(string $tokenHache): ?array
    {
        return $this->trouverUnParCritere(['token_validation_email' => $tokenHache]);
    }

    /**
     * Vérifie si un login est déjà utilisé par un autre utilisateur.
     * Essentiel pour garantir l'unicité lors de la création ou de la mise à jour.
     *
     * @param string $login Le login à vérifier.
     * @param string|null $excludeUserId L'ID de l'utilisateur à exclure de la recherche (pour les mises à jour).
     * @return bool Vrai si le login est déjà pris, faux sinon.
     */
    public function loginExiste(string $login, ?string $excludeUserId = null): bool
    {
        $criteres = ['login_utilisateur' => $login];
        if ($excludeUserId) {
            $criteres['numero_utilisateur'] = ['operator' => '!=', 'value' => $excludeUserId];
        }
        return $this->trouverUnParCritere($criteres) !== null;
    }

    /**
     * Vérifie si un email est déjà utilisé par un autre utilisateur.
     *
     * @param string $email L'email à vérifier.
     * @param string|null $excludeUserId L'ID de l'utilisateur à exclure de la recherche.
     * @return bool Vrai si l'email est déjà pris, faux sinon.
     */
    public function emailExiste(string $email, ?string $excludeUserId = null): bool
    {
        $criteres = ['email_principal' => $email];
        if ($excludeUserId) {
            $criteres['numero_utilisateur'] = ['operator' => '!=', 'value' => $excludeUserId];
        }
        return $this->trouverUnParCritere($criteres) !== null;
    }
}