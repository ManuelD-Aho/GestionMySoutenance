<?php
namespace App\Backend\Model;

use PDO;

class Occuper extends BaseModel
{
    public string $table = 'occuper';
    public string|array $primaryKey = ['id_fonction', 'numero_enseignant'];

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }

    /**
     * Trouve une occupation par ses clés.
     * @param string $idFonction
     * @param string $numeroEnseignant
     * @return array|null
     */
    public function trouverOccupationParCles(string $idFonction, string $numeroEnseignant): ?array
    {
        return $this->trouverUnParCritere([
            'id_fonction' => $idFonction,
            'numero_enseignant' => $numeroEnseignant
        ]);
    }
}