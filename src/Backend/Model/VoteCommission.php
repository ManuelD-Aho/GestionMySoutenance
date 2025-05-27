<?php

namespace Backend\Model;


use PDO;
use App\Backend\Model\BaseModel;

class VoteCommission extends BaseModel
{
    protected string $table = 'vote_commission';
    protected string $clePrimaire = 'id_vote';
}