<?php

namespace App\Backend\Model;

use PDO;

class CritereConformiteRef extends BaseModel
{
    protected string $table = 'critere_conformite_ref';
    protected string|array $primaryKey = 'id_critere';

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }
}