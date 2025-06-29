<?php
namespace App\Backend\Model;

use PDO;

class Utilisateur extends BaseModel
{
    public string $table = 'utilisateur';
    public string|array $primaryKey = 'numero_utilisateur';

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }

    /**
     * Trouve un utilisateur par son login ou son email principal.
     * @param string $identifiant Le login ou l'email principal.
     * @return array|null
     */
    public function trouverParLoginOuEmailPrincipal(string $identifiant): ?array
    {
        return $this->trouverUnParCritere([
            'login_utilisateur' => $identifiant,
            'email_principal' => $identifiant
        ], ['*'], 'OR');
    }

    /**
     * Vérifie si un login existe déjà, en excluant éventuellement un utilisateur.
     * @param string $login
     * @param string|null $excludeId
     * @return bool
     */
    public function loginExiste(string $login, ?string $excludeId = null): bool
    {
        $criteres = ['login_utilisateur' => $login];
        if ($excludeId !== null) {
            $criteres['numero_utilisateur'] = ['operator' => '!=', 'value' => $excludeId];
        }
        return $this->compterParCritere($criteres) > 0;
    }

    /**
     * Trouve un utilisateur par son token de réinitialisation de mot de passe (haché).
     * @param string $tokenHache Le token de réinitialisation de mot de passe haché.
     * @return array|null
     */
    public function trouverParTokenResetMdp(string $tokenHache): ?array
    {
        return $this->trouverUnParCritere(['token_reset_mdp' => $tokenHache]);
    }
}