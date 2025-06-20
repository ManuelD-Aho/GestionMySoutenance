<?php
namespace App\Backend\Model;

use PDO;

class Penalite extends BaseModel
{
    protected string $table = 'penalite';
    protected string|array $primaryKey = 'id_penalite'; // Clé primaire VARCHAR(50)

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }

    /**
     * Trouve les pénalités pour un étudiant donné.
     * @param string $numeroCarteEtudiant Le numéro de carte de l'étudiant.
     * @param array $colonnes Les colonnes à sélectionner.
     * @return array Liste des pénalités trouvées.
     */
    public function trouverPenalitesEtudiant(string $numeroCarteEtudiant, array $colonnes = ['*']): array
    {
        return $this->trouverParCritere(['numero_carte_etudiant' => $numeroCarteEtudiant], $colonnes);
    }

    /**
     * Trouve les pénalités non régularisées pour un étudiant.
     * @param string $numeroCarteEtudiant Le numéro de carte de l'étudiant.
     * @param array $colonnes Les colonnes à sélectionner.
     * @return array Liste des pénalités non régularisées.
     */
    public function trouverPenalitesNonRegul(string $numeroCarteEtudiant, array $colonnes = ['*']): array
    {
        // Supposons que 'PEN_DUE' est la valeur de référence pour les pénalités dues
        return $this->trouverParCritere([
            'numero_carte_etudiant' => $numeroCarteEtudiant,
            'id_statut_penalite' => 'PEN_DUE'
        ], $colonnes);
    }
}