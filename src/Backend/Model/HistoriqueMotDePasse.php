<?php
namespace App\Backend\Model;

use PDO;

class HistoriqueMotDePasse extends BaseModel
{
    public string $table = 'historique_mot_de_passe';
    public string|array $primaryKey = 'id_historique_mdp';

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }

    /**
     * Récupère les derniers mots de passe pour un utilisateur.
     * @param string $numeroUtilisateur
     * @param int $limite
     * @return array
     */
    public function recupererHistoriquePourUtilisateur(string $numeroUtilisateur, int $limite = 5): array
    {
        return $this->trouverParCritere(
            ['numero_utilisateur' => $numeroUtilisateur],
            ['mot_de_passe_hache'],
            'AND',
            'date_changement DESC',
            $limite
        );
    }
}