<?php

namespace Backend\Model;

use PDO;

class Specialite extends BaseModel
{
    protected string $table = 'specialite';
    protected string $clePrimaire = 'id_specialite';
}