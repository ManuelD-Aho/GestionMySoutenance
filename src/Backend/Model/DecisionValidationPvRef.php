<?php

namespace App\Backend\Model;

use PDO;
use App\Backend\Model\BaseModel;

class DecisionValidationPvRef extends BaseModel
{
    protected string $table = 'decision_validation_pv_ref';
    protected string $clePrimaire = 'id_decision_validation_pv';
}