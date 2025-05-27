<?php

namespace App\Backend\Model;

use PDO;
use App\Backend\Model\BaseModel;

class Specialite extends BaseModel
{
    protected string $table = 'specialite';
    protected string $clePrimaire = 'id_specialite';
}