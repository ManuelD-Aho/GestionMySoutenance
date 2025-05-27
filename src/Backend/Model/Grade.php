<?php

namespace App\Backend\Model;

use PDO;
use App\Backend\Model\BaseModel;

class Grade extends BaseModel
{
    protected string $table = 'grade';
    protected string $clePrimaire = 'id_grade';
}