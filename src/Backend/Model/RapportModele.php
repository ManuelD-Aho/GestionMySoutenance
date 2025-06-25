<?php
namespace App\Backend\Model;

use PDO;

class RapportModele extends BaseModel
{
    protected string $table = 'rapport_modele';
    protected string|array $primaryKey = 'id_modele'; // Clé primaire simple

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }

    /**
     * Liste les modèles de rapport par statut.
     * @param string $statut Le statut du modèle (ex: 'Brouillon', 'Publié', 'Archivé').
     * @param array $colonnes Les colonnes à sélectionner.
     * @return array Liste des modèles.
     */
    public function trouverModelesParStatut(string $statut, array $colonnes = ['*']): array
    {
        return $this->trouverParCritere(['statut' => $statut], $colonnes);
    }

    /**
     * Trouve un modèle de rapport publié spécifique.
     * @param string $idModele L'ID du modèle.
     * @param array $colonnes Les colonnes à sélectionner.
     * @return array|null Le modèle publié ou null.
     */
    public function trouverModelePublie(string $idModele, array $colonnes = ['*']): ?array
    {
        return $this->trouverUnParCritere(['id_modele' => $idModele, 'statut' => 'Publié'], $colonnes);
    }
}