<?php

namespace App\Backend\Model;

use PDO;
use App\Backend\Model\BaseModel;

class GroupeUtilisateur extends BaseModel
{
    protected string $table = 'groupe_utilisateur';
    protected string $clePrimaire = 'id_groupe_utilisateur';
}