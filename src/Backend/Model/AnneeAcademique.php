<?php
namespace App\Backend\Model;

use PDO;

class AnneeAcademique extends BaseModel
{
    protected string $table = 'annee_academique';
    protected string|array $primaryKey = 'id_annee_academique'; // Clé primaire de type string

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }
    // Hérite des méthodes CRUD de BaseModel. Pas de méthodes spécifiques à ajouter ici.
}