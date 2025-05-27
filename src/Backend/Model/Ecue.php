<?php

namespace Backend\Model;

use PDO;

class Ecue extends BaseModel
{
    protected string $table = 'ecue';
    protected string $clePrimaire = 'id_ecue';
}