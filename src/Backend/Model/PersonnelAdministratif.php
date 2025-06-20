<?php
namespace App\Backend\Model;

use PDO;

class PersonnelAdministratif extends BaseModel
{
    protected string $table = 'personnel_administratif';
    protected string|array $primaryKey = 'numero_personnel_administratif'; // Clé primaire de type string

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }

    /**
     * Trouve un membre du personnel administratif par son numéro unique.
     * @param string $numeroPersonnelAdministratif Le numéro du personnel administratif.
     * @param array $colonnes Les colonnes à sélectionner.
     * @return array|null Les données du personnel ou null si non trouvé.
     */
    public function trouverParNumeroPersonnelAdministratif(string $numeroPersonnelAdministratif, array $colonnes = ['*']): ?array
    {
        return $this->trouverUnParCritere(['numero_personnel_administratif' => $numeroPersonnelAdministratif], $colonnes);
    }
}