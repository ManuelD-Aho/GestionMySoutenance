<?php
namespace App\Backend\Model;

use PDO;

class Traitement extends BaseModel
{
    protected string $table = 'traitement';
    protected string|array $primaryKey = 'id_traitement';

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }
}