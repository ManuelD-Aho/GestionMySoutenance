<?php
namespace App\Backend\Model;

use PDO;

class Affecter extends BaseModel
{
    protected string $table = 'affecter';
    protected string|array $primaryKey = ['numero_enseignant', 'id_rapport_etudiant', 'id_statut_jury'];

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }
}