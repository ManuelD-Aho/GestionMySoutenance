<?php
namespace App\Backend\Model;

use PDO;

class Ecue extends BaseModel
{
    public string $table = 'ecue';
    public string|array $primaryKey = 'id_ecue';

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }
}