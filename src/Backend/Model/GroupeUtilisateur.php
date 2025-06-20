<?php
namespace App\Backend\Model;

use PDO;

class GroupeUtilisateur extends BaseModel
{
    protected string $table = 'groupe_utilisateur';
    protected string|array $primaryKey = 'id_groupe_utilisateur'; // Clé primaire de type string

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }
    // Hérite des méthodes CRUD de BaseModel. Pas de méthodes spécifiques à ajouter ici.
}