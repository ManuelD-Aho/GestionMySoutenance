<?php
namespace App\Backend\Model;

use PDO;

class SessionValidation extends BaseModel
{
    public string $table = 'session_validation';
    public string|array $primaryKey = 'id_session';

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }
}