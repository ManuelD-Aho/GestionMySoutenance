<?php

namespace App\Backend\Model;

use PDO;
use App\Backend\Model\BaseModel;

class DecisionVoteRef extends BaseModel
{
    protected string $table = 'decision_vote_ref';
    protected string $clePrimaire = 'id_decision_vote';
}