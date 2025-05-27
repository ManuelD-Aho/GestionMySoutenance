<?php

namespace App\Backend\Model;

use PDO;
use App\Backend\Model\BaseModel;

class NiveauEtude extends BaseModel
{
    protected string $table = 'niveau_etude';
    protected string $clePrimaire = 'id_niveau_etude';
}