<?php

namespace App\Backend\Model;

use PDO;
use App\Backend\Model\BaseModel;

class StatutPvRef extends BaseModel
{
    protected string $table = 'statut_pv_ref';
    protected string $clePrimaire = 'id_statut_pv';
}