<?php
namespace App\Backend\Model;

use PDO;

class GroupeUtilisateur extends BaseModel
{
    protected string $table = 'groupe_utilisateur';
    protected string|array $primaryKey = 'id_groupe_utilisateur';

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }
}