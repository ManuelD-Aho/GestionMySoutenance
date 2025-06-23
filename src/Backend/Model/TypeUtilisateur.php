<?php
namespace App\Backend\Model;

use PDO;

class TypeUtilisateur extends BaseModel
{
    protected string $table = 'type_utilisateur';
    protected string|array $primaryKey = 'id_type_utilisateur';

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }
}