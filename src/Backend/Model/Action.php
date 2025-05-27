<?php

namespace App\Backend\Model;

use PDO;
use App\Backend\Model\BaseModel;

class Action extends BaseModel
{
    protected string $table = 'action';
    protected string $clePrimaire = 'id_action';
}