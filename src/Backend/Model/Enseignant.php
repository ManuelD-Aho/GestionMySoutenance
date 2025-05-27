<?php

namespace Backend\Model;

use PDO;

class Enseignant extends BaseModel
{
    protected string $table = 'enseignant';
    protected string $clePrimaire = 'numero_enseignant';
}