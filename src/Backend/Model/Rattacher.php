<?php
namespace App\Backend\Model;

use PDO;

class Rattacher extends BaseModel
{
    public string $table = 'rattacher';
    public string|array $primaryKey = ['id_groupe_utilisateur', 'id_traitement'];

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }
}