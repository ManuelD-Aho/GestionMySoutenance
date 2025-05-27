<?php

namespace App\Backend\Model;

use PDO;

class CompteRendu extends BaseModel
{
    protected string $table = 'compte_rendu';
    protected string $clePrimaire = 'id_compte_rendu';
}