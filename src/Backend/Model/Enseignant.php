<?php
namespace App\Backend\Model;

use PDO;

class Enseignant extends BaseModel
{
    protected string $table = 'enseignant';
    protected string|array $primaryKey = 'numero_enseignant'; // Clé primaire de type string

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }

    /**
     * Trouve un enseignant par son numéro unique.
     * @param string $numeroEnseignant Le numéro d'enseignant.
     * @param array $colonnes Les colonnes à sélectionner.
     * @return array|null Les données de l'enseignant ou null si non trouvé.
     */
    public function trouverParNumeroEnseignant(string $numeroEnseignant, array $colonnes = ['*']): ?array
    {
        return $this->trouverUnParCritere(['numero_enseignant' => $numeroEnseignant], $colonnes);
    }
}