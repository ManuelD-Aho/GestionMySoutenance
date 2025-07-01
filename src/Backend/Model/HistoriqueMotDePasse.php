<?php
// src/Backend/Model/HistoriqueMotDePasse.php

namespace App\Backend\Model;

use PDO;

class HistoriqueMotDePasse extends BaseModel
{
    protected string $table = 'historique_mot_de_passe';
    protected string|array $primaryKey = 'id_historique_mdp';
    protected array $fields = [
        'id_historique_mdp', 'numero_utilisateur', 'mot_de_passe_hache', 'date_changement'
    ];

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }

    /**
     * Récupère les N derniers mots de passe hachés pour un utilisateur.
     * Utilisé pour empêcher la réutilisation des mots de passe récents.
     *
     * @param string $numeroUtilisateur L'ID de l'utilisateur.
     * @param int $limite Le nombre d'entrées à récupérer (ex: les 3 derniers).
     * @return array La liste des mots de passe hachés.
     */
    public function recupererHistoriquePourUtilisateur(string $numeroUtilisateur, int $limite = 3): array
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