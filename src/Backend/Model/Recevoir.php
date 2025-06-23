<?php
namespace App\Backend\Model;

use PDO;

class Recevoir extends BaseModel
{
    protected string $table = 'recevoir';
    protected string|array $primaryKey = 'id_reception';

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }
}