<?php
namespace App\Backend\Model;

use PDO;

class FaireStage extends BaseModel
{
    protected string $table = 'faire_stage';
    protected string|array $primaryKey = ['id_entreprise', 'numero_carte_etudiant'];

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }
}