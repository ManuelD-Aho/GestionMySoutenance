<?php
namespace App\Backend\Model;

use PDO;

class ParticipantConversation extends BaseModel
{
    protected string $table = 'participant_conversation';
    protected string|array $primaryKey = ['id_conversation', 'numero_utilisateur'];

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }
}