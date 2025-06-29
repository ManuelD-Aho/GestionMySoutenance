<?php
namespace App\Backend\Model;

use PDO;

class RapportEtudiant extends BaseModel
{
    public string $table = 'rapport_etudiant';
    public string|array $primaryKey = 'id_rapport_etudiant';

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }
}