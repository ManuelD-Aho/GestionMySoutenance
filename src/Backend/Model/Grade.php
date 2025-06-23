<?php
namespace App\Backend\Model;

use PDO;

class Grade extends BaseModel
{
    protected string $table = 'grade';
    protected string|array $primaryKey = 'id_grade';

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }
}