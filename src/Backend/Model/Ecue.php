<?php
namespace App\Backend\Model;

use PDO;

class Ecue extends BaseModel
{
    protected string $table = 'ecue';
    protected string|array $primaryKey = 'id_ecue';

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }
}