<?php
namespace App\Backend\Model;

use PDO;

class Grade extends BaseModel
{
    public string $table = 'grade';
    public string|array $primaryKey = 'id_grade';

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }
}