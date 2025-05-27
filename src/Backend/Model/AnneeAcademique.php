<?php

namespace App\Backend\Model;
use PDO;

class AnneeAcademique extends BaseModel
{
    protected string $table = 'annee_academique';
    protected string $clePrimaire = 'id_annee_academique';
}