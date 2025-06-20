<?php
namespace App\Backend\Model;

use PDO;

class CompteRendu extends BaseModel
{
    protected string $table = 'compte_rendu';
    protected string|array $primaryKey = 'id_compte_rendu'; // Clé primaire de type string

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }
    // Hérite des méthodes CRUD de BaseModel. Pas de méthodes spécifiques à ajouter ici.
}