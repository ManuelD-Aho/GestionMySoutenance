<?php

namespace App\Backend\Model;

use PDO;

class ParametreSysteme extends BaseModel
{
    protected string $table = 'parametres_systeme';
    protected string|array $primaryKey = 'cle';

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }
}