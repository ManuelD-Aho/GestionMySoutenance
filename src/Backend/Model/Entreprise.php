<?php
namespace App\Backend\Model;

use PDO;

class Entreprise extends BaseModel
{
    public string $table = 'entreprise';
    public string|array $primaryKey = 'id_entreprise';

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }
}