<?php
namespace App\Backend\Model;

use PDO;

class Rattacher extends BaseModel
{
    protected string $table = 'rattacher';
    protected string|array $primaryKey = ['id_groupe_utilisateur', 'id_traitement'];

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }
}