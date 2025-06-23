<?php
namespace App\Backend\Model;

use PDO;

class Attribuer extends BaseModel
{
    protected string $table = 'attribuer';
    protected string|array $primaryKey = ['numero_enseignant', 'id_specialite'];

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }
}