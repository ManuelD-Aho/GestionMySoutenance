<?php
namespace App\Backend\Model;

use PDO;

class TypeUtilisateur extends BaseModel
{
    public string $table = 'type_utilisateur';
    public string|array $primaryKey = 'id_type_utilisateur';

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }
}