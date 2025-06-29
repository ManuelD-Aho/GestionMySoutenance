<?php
namespace App\Backend\Model;

use PDO;

class Specialite extends BaseModel
{
    public string $table = 'specialite';
    public string|array $primaryKey = 'id_specialite';

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }
}