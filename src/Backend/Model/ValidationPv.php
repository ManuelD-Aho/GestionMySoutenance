<?php
namespace App\Backend\Model;

use PDO;

class ValidationPv extends BaseModel
{
    public string $table = 'validation_pv';
    public string|array $primaryKey = ['id_compte_rendu', 'numero_enseignant'];

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }

    /**
     * Trouve une validation de PV par ses clés.
     * @param string $idCompteRendu
     * @param string $numeroEnseignant
     * @return array|null
     */
    public function trouverValidationPvParCles(string $idCompteRendu, string $numeroEnseignant): ?array
    {
        return $this->trouverUnParCritere([
            'id_compte_rendu' => $idCompteRendu,
            'numero_enseignant' => $numeroEnseignant
        ]);
    }
}