<?php

namespace App\Backend\Model;

use PDO;
use App\Backend\Model\BaseModel;

class Entreprise extends BaseModel
{
    protected string $table = 'entreprise';
    protected string $clePrimaire = 'id_entreprise';
}