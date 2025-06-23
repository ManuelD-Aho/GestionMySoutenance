<?php
namespace App\Backend\Model;

use PDO;

class Action extends BaseModel
{
    protected string $table = 'action';
    protected string|array $primaryKey = 'id_action';

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }
}