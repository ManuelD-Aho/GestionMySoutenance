<?php
namespace App\Backend\Model;

use PDO;

class Sessions extends BaseModel
{
    public string $table = 'sessions';
    public string|array $primaryKey = 'session_id';

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }

    /**
     * Trouve les sessions actives pour un utilisateur.
     * @param string $userId
     * @return array
     */
    public function trouverSessionsParUtilisateur(string $userId): array
    {
        return $this->trouverParCritere(['user_id' => $userId]);
    }
}