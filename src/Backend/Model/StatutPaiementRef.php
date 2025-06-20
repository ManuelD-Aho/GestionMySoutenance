<?php
namespace App\Backend\Model;

use PDO;

class StatutPaiementRef extends BaseModel
{
    protected string $table = 'statut_paiement_ref';
    protected string|array $primaryKey = 'id_statut_paiement'; // Clé primaire de type string

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }
    // Hérite des méthodes CRUD de BaseModel. Pas de méthodes spécifiques à ajouter ici.
}