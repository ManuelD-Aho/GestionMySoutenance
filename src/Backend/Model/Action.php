<?php

namespace App\Backend\Model;
use PDO;

class Action extends BaseModel
{
    protected string $table = 'action';
    protected string $clePrimaire = 'id_action';
}