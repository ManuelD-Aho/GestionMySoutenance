<?php

namespace App\Backend\Model;
use PDO;

class VoteCommission extends BaseModel
{
    protected string $table = 'vote_commission';
    protected string $clePrimaire = 'id_vote';
}