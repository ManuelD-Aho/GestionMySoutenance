<?php

namespace Backend\Model;

use PDO;

class Utilisateur extends BaseModel
{
    protected string $table = 'utilisateur';
    protected string $clePrimaire = 'numero_utilisateur';
}