<?php

namespace App\Backend\Model;

use PDO;
use App\Backend\Model\BaseModel;

class AnneeAcademique extends BaseModel
{
    protected string $table = 'annee_academique';
    protected string $clePrimaire = 'id_annee_academique';
}