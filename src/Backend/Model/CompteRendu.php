<?php
namespace App\Backend\Model;

use PDO;

class CompteRendu extends BaseModel
{
    public string $table = 'compte_rendu';
    public string|array $primaryKey = 'id_compte_rendu';

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }
}