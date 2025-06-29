<?php
namespace App\Backend\Model;

use PDO;

class CritereConformiteRef extends BaseModel
{
    public string $table = 'critere_conformite_ref';
    public string|array $primaryKey = 'id_critere';

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }
}