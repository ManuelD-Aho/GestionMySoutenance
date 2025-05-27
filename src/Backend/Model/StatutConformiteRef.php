<?php

namespace App\Backend\Model;

use PDO;
use App\Backend\Model\BaseModel;

class StatutConformiteRef extends BaseModel
{
    protected string $table = 'statut_conformite_ref';
    protected string $clePrimaire = 'id_statut_conformite';
}