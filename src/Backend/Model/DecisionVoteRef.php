<?php
namespace App\Backend\Model;

use PDO;

class DecisionVoteRef extends BaseModel
{
    public string $table = 'decision_vote_ref';
    public string|array $primaryKey = 'id_decision_vote';

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }
}