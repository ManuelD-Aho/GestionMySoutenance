<?php

namespace Backend\Model;

use PDO;

class DecisionVoteRef extends BaseModel
{
    protected string $table = 'decision_vote_ref';
    protected string $clePrimaire = 'id_decision_vote';
}