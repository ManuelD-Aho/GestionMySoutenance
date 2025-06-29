<?php
namespace App\Backend\Model;

use PDO;

class RapportModele extends BaseModel
{
    public string $table = 'rapport_modele';
    public string|array $primaryKey = 'id_modele';

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }
}