<?php

namespace App\Backend\Model;

use PDO;
use App\Backend\Model\BaseModel;

class RapportEtudiant extends BaseModel
{
    protected string $table = 'rapport_etudiant';
    protected string $clePrimaire = 'id_rapport_etudiant';
}