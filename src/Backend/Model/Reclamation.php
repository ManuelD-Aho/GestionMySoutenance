<?php

namespace App\Backend\Model;

use PDO;
use App\Backend\Model\BaseModel;

class Reclamation extends BaseModel
{
    protected string $table = 'reclamation';
    protected string $clePrimaire = 'id_reclamation';
}