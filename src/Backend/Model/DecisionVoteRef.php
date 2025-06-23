<?php
namespace App\Backend\Model;

use PDO;

class DecisionVoteRef extends BaseModel
{
    protected string $table = 'decision_vote_ref';
    protected string|array $primaryKey = 'id_decision_vote';

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }
}