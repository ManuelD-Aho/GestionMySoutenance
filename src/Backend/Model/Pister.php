<?php
namespace App\Backend\Model;

use PDO;

class Pister extends BaseModel
{
    protected string $table = 'pister';
    protected string|array $primaryKey = 'id_piste';

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }
}