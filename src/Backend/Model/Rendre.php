<?php
namespace App\Backend\Model;

use PDO;

class Rendre extends BaseModel
{
    protected string $table = 'rendre';
    protected string|array $primaryKey = ['numero_enseignant', 'id_compte_rendu'];

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }
}