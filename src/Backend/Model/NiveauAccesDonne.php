<?php
namespace App\Backend\Model;

use PDO;

class NiveauAccesDonne extends BaseModel
{
    public string $table = 'niveau_acces_donne';
    public string|array $primaryKey = 'id_niveau_acces_donne';

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }
}