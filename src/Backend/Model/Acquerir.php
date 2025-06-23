<?php
namespace App\Backend\Model;

use PDO;

class Acquerir extends BaseModel
{
    protected string $table = 'acquerir';
    protected string|array $primaryKey = ['id_grade', 'numero_enseignant'];

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }
}