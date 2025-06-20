<?php
namespace App\Backend\Model;

use PDO;

class TypeUtilisateur extends BaseModel
{
    protected string $table = 'type_utilisateur';
    protected string|array $primaryKey = 'id_type_utilisateur'; // Clé primaire de type string

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }
    // Hérite des méthodes CRUD de BaseModel. Pas de méthodes spécifiques à ajouter ici.
}