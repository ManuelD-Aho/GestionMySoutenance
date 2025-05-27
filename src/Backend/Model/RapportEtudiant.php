<?php

namespace Backend\Model;

use PDO;

class RapportEtudiant extends BaseModel
{
    protected string $table = 'rapport_etudiant';
    protected string $clePrimaire = 'id_rapport_etudiant';
}