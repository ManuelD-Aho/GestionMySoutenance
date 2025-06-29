<?php
namespace App\Backend\Model;

use PDO;

class Enregistrer extends BaseModel
{
    public string $table = 'enregistrer';
    public string|array $primaryKey = 'id_enregistrement';

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }
}