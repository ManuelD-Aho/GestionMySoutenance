<?php
namespace App\Backend\Model;

use PDO;

class DecisionValidationPvRef extends BaseModel
{
    public string $table = 'decision_validation_pv_ref';
    public string|array $primaryKey = 'id_decision_validation_pv';

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }
}