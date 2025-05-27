<?php

namespace Backend\Model;

use PDO;

class Etudiant extends BaseModel
{
    protected string $table = 'etudiant';
    protected string $clePrimaire = 'numero_carte_etudiant';
}