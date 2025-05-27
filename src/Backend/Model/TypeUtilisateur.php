<?php

namespace App\Backend\Model;
use PDO;
class TypeUtilisateur extends BaseModel
{
    protected string $table = 'type_utilisateur';
    protected string $clePrimaire = 'id_type_utilisateur';
}