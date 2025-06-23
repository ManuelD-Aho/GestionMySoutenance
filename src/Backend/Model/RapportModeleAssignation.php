<?php
namespace App\Backend\Model;

use PDO;

class RapportModeleAssignation extends BaseModel
{
    protected string $table = 'rapport_modele_assignation';
    protected string|array $primaryKey = ['id_modele', 'id_niveau_etude'];

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }
}