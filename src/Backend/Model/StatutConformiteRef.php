<?php
namespace App\Backend\Model;

use PDO;

class StatutConformiteRef extends BaseModel
{
    public string $table = 'statut_conformite_ref';
    public string|array $primaryKey = 'id_statut_conformite';

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }
}