<?php
namespace App\Backend\Model;

use PDO;

class DecisionPassageRef extends BaseModel
{
    public string $table = 'decision_passage_ref';
    public string|array $primaryKey = 'id_decision_passage';

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }
}