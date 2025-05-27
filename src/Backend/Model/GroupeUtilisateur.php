<?php

namespace Backend\Model;

use PDO;

class GroupeUtilisateur extends BaseModel
{
    protected string $table = 'groupe_utilisateur';
    protected string $clePrimaire = 'id_groupe_utilisateur';
}