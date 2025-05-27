<?php

namespace App\Backend\Model;

use PDO;
use App\Backend\Model\BaseModel;

class NiveauApprobation extends BaseModel
{
    protected string $table = 'niveau_approbation';
    protected string $clePrimaire = 'id_niveau_approbation';
}