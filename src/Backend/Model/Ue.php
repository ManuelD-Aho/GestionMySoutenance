<?php

namespace Backend\Model;

use PDO;

class Ue extends BaseModel
{
    protected string $table = 'ue';
    protected string $clePrimaire = 'id_ue';
}