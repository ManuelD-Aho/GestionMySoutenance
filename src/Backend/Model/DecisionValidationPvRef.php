<?php

namespace Backend\Model;

use PDO;

class DecisionValidationPvRef extends BaseModel
{
    protected string $table = 'decision_validation_pv_ref';
    protected string $clePrimaire = 'id_decision_validation_pv';
}