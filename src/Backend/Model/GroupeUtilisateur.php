<?php
namespace App\Backend\Model;

use PDO;

class GroupeUtilisateur extends BaseModel
{
    public string $table = 'groupe_utilisateur';
    public string|array $primaryKey = 'id_groupe_utilisateur';

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }
}