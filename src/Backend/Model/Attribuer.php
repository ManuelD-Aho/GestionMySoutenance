<?php
namespace App\Backend\Model;

use PDO;

class Attribuer extends BaseModel
{
    public string $table = 'attribuer';
    public string|array $primaryKey = ['numero_enseignant', 'id_specialite'];

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }

    /**
     * Trouve une attribution par ses clés.
     * @param string $numeroEnseignant
     * @param string $idSpecialite
     * @return array|null
     */
    public function trouverAttributionParCles(string $numeroEnseignant, string $idSpecialite): ?array
    {
        return $this->trouverUnParCritere([
            'numero_enseignant' => $numeroEnseignant,
            'id_specialite' => $idSpecialite
        ]);
    }
}