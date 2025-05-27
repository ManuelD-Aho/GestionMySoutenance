<?php

namespace Backend\Model;

use PDO;

class Grade extends BaseModel
{
    protected string $table = 'grade';
    protected string $clePrimaire = 'id_grade';
}