<?php

namespace App\Backend\Model;

use PDO;
use App\Backend\Model\BaseModel;

class DecisionPassageRef extends BaseModel
{
    protected string $table = 'decision_passage_ref';
    protected string $clePrimaire = 'id_decision_passage';
}