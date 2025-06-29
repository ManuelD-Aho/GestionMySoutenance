<?php
namespace App\Backend\Model;

use PDO;

class AnneeAcademique extends BaseModel
{
    public string $table = 'annee_academique';
    public string|array $primaryKey = 'id_annee_academique';

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }
}