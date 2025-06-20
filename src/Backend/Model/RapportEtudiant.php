<?php
namespace App\Backend\Model;

use PDO;

class RapportEtudiant extends BaseModel
{
    protected string $table = 'rapport_etudiant';
    protected string|array $primaryKey = 'id_rapport_etudiant'; // Clé primaire de type string

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }
    // Hérite des méthodes CRUD de BaseModel. Pas de méthodes spécifiques à ajouter ici.
}