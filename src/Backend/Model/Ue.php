<?php
namespace App\Backend\Model;

use PDO;

class Ue extends BaseModel
{
    protected string $table = 'ue';
    protected string|array $primaryKey = 'id_ue';

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }
}