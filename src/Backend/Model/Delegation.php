<?php
namespace App\Backend\Model;

use PDO;

class Delegation extends BaseModel
{
    public string $table = 'delegation';
    public string|array $primaryKey = 'id_delegation';

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }
}