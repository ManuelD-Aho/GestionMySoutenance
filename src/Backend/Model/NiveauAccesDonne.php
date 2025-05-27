<?php

namespace App\Backend\Model;

use PDO;
use App\Backend\Model\BaseModel;

class NiveauAccesDonne extends BaseModel
{
    protected string $table = 'niveau_acces_donne';
    protected string $clePrimaire = 'id_niveau_acces_donne';
}