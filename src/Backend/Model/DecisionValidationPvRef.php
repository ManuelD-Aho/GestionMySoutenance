<?php
namespace App\Backend\Model;

use PDO;

class DecisionValidationPvRef extends BaseModel
{
    protected string $table = 'decision_validation_pv_ref';
    protected string|array $primaryKey = 'id_decision_validation_pv';

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }
}