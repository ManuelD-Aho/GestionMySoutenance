<?php
// src/Backend/Model/Sessions.php

namespace App\Backend\Model;

use PDO;

class Sessions extends BaseModel
{
    public string $table = 'sessions';
    protected string|array $primaryKey = 'session_id';
    protected array $fields = [
        'session_id', 'session_data', 'session_last_activity', 'session_lifetime', 'user_id'
    ];

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }

    public function trouverSessionsParUtilisateur(string $userId): array
    {
        return $this->trouverParCritere(['user_id' => $userId]);
    }
}