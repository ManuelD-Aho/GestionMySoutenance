<?php
namespace App\Backend\Model;

use PDO;

class ValidationPv extends BaseModel
{
    protected string $table = 'validation_pv';
    protected string|array $primaryKey = ['id_compte_rendu', 'numero_enseignant'];

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }
}