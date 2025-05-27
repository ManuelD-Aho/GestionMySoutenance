<?php

namespace Backend\Model;

use PDO;

class Reclamation extends BaseModel
{
    protected string $table = 'reclamation';
    protected string $clePrimaire = 'id_reclamation';
}