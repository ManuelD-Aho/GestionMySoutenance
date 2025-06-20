<?php
namespace App\Backend\Model;

use PDO;

class DecisionVoteRef extends BaseModel
{
    protected string $table = 'decision_vote_ref';
    protected string|array $primaryKey = 'id_decision_vote'; // Clé primaire de type string

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }
    // Hérite des méthodes CRUD de BaseModel. Pas de méthodes spécifiques à ajouter ici.
}