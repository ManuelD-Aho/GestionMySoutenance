<?php

namespace App\Backend\Model;

use PDO;
use App\Backend\Model\BaseModel;

class StatutRapportRef extends BaseModel
{
    protected string $table = 'statut_rapport_ref';
    protected string $clePrimaire = 'id_statut_rapport';
}