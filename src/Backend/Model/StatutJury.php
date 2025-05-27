<?php

namespace App\Backend\Model;

use PDO;
use App\Backend\Model\BaseModel;

class StatutJury extends BaseModel
{
    protected string $table = 'statut_jury';
    protected string $clePrimaire = 'id_statut_jury';
}