<?php
namespace App\Backend\Model;

use PDO;

class Action extends BaseModel
{
    public string $table = 'action';
    public string|array $primaryKey = 'id_action';

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }
}