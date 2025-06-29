<?php
namespace App\Backend\Model;

use PDO;

class Recevoir extends BaseModel
{
    public string $table = 'recevoir';
    public string|array $primaryKey = 'id_reception';

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }
}