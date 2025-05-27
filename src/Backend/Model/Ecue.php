<?php

namespace App\Backend\Model;

use PDO;
use App\Backend\Model\BaseModel;

class Ecue extends BaseModel
{
    protected string $table = 'ecue';
    protected string $clePrimaire = 'id_ecue';
}