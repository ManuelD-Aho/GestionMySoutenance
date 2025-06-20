<?php
namespace App\Backend\Model;

use PDO;

class Etudiant extends BaseModel
{
    protected string $table = 'etudiant';
    protected string|array $primaryKey = 'numero_carte_etudiant'; // Clé primaire de type string

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }

    /**
     * Trouve un étudiant par son numéro de carte étudiant.
     * @param string $numeroCarteEtudiant Le numéro de carte étudiant.
     * @param array $colonnes Les colonnes à sélectionner.
     * @return array|null Les données de l'étudiant ou null si non trouvé.
     */
    public function trouverParNumeroCarteEtudiant(string $numeroCarteEtudiant, array $colonnes = ['*']): ?array
    {
        return $this->trouverUnParCritere(['numero_carte_etudiant' => $numeroCarteEtudiant], $colonnes);
    }
}