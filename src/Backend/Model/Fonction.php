<?php
namespace App\Backend\Model;

use PDO;

class Fonction extends BaseModel
{
    protected string $table = 'fonction';
    protected string|array $primaryKey = 'id_fonction';

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }
}