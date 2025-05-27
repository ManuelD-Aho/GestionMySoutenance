<?php

namespace App\Backend\Model;

use PDO;
use App\Backend\Model\BaseModel;

class Fonction extends BaseModel
{
    protected string $table = 'fonction';
    protected string $clePrimaire = 'id_fonction';
}