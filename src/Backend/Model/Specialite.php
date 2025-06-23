<?php
namespace App\Backend\Model;

use PDO;

class Specialite extends BaseModel
{
    protected string $table = 'specialite';
    protected string|array $primaryKey = 'id_specialite';

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }
}