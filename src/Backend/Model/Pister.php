<?php
namespace App\Backend\Model;

use PDO;

class Pister extends BaseModel
{
    public string $table = 'pister';
    public string|array $primaryKey = 'id_piste';

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }
}