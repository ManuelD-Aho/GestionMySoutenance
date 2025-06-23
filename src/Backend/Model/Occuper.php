<?php
namespace App\Backend\Model;

use PDO;

class Occuper extends BaseModel
{
    protected string $table = 'occuper';
    protected string|array $primaryKey = ['id_fonction', 'numero_enseignant'];

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }
}