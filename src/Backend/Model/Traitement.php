<?php

namespace App\Backend\Model;
use PDO;

class Traitement extends BaseModel
{
    protected string $table = 'traitement';
    protected string $clePrimaire = 'id_traitement';
}