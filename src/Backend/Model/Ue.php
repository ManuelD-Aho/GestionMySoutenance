<?php
namespace App\Backend\Model;

use PDO;

class Ue extends BaseModel
{
    protected string $table = 'ue';
    protected string|array $primaryKey = 'id_ue'; // Clé primaire de type string

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }
    // Hérite des méthodes CRUD de BaseModel. Pas de méthodes spécifiques à ajouter ici.
}