<?php
namespace App\Backend\Model;

use PDO;

class Fonction extends BaseModel
{
    public string $table = 'fonction';
    public string|array $primaryKey = 'id_fonction';

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }
}