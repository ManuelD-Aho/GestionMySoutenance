<?php
namespace App\Backend\Model;

use PDO;

class NiveauEtude extends BaseModel
{
    protected string $table = 'niveau_etude';
    protected string|array $primaryKey = 'id_niveau_etude'; // Clé primaire de type string

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }
    // Hérite des méthodes CRUD de BaseModel. Pas de méthodes spécifiques à ajouter ici.
}