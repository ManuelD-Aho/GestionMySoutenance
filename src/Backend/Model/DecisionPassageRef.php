<?php

namespace Backend\Model;

use PDO;

class DecisionPassageRef extends BaseModel
{
    protected string $table = 'decision_passage_ref';
    protected string $clePrimaire = 'id_decision_passage';
}