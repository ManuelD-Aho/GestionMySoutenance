<?php
namespace App\Backend\Model;

use PDO;

class NiveauAccesDonne extends BaseModel
{
    protected string $table = 'niveau_acces_donne';
    protected string|array $primaryKey = 'id_niveau_acces_donne'; // Clé primaire de type string

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }
    // Hérite des méthodes CRUD de BaseModel. Pas de méthodes spécifiques à ajouter ici.
}