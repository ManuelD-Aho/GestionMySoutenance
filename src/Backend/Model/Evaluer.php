<?php
namespace App\Backend\Model;

use PDO;

class Evaluer extends BaseModel
{
    protected string $table = 'evaluer';
    protected string|array $primaryKey = ['numero_carte_etudiant', 'id_ecue', 'id_annee_academique'];

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }
}