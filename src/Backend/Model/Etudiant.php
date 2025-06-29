<?php
namespace App\Backend\Model;

use PDO;

class Etudiant extends BaseModel
{
    public string $table = 'etudiant';
    public string|array $primaryKey = 'numero_carte_etudiant';

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }
}