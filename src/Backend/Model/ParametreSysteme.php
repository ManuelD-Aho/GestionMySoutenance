<?php
namespace App\Backend\Model;

use PDO;

class ParametreSysteme extends BaseModel
{
    public string $table = 'parametres_systeme';
    public string|array $primaryKey = 'cle';

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }
}