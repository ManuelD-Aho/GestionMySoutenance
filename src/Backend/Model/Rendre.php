<?php
namespace App\Backend\Model;

use PDO;

class Rendre extends BaseModel
{
    public string $table = 'rendre';
    public string|array $primaryKey = ['numero_enseignant', 'id_compte_rendu'];

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }
}