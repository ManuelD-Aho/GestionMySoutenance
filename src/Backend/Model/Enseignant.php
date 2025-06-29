<?php
namespace App\Backend\Model;

use PDO;

class Enseignant extends BaseModel
{
    public string $table = 'enseignant';
    public string|array $primaryKey = 'numero_enseignant';

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }
}