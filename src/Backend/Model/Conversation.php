<?php
namespace App\Backend\Model;

use PDO;

class Conversation extends BaseModel
{
    protected string $table = 'conversation';
    protected string|array $primaryKey = 'id_conversation';

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }

    public function getParticipants(): array
    {
        if (!isset($this->id_conversation)) return [];
        $participantModel = new ParticipantConversation($this->db);
        return $participantModel->trouverParCritere(['id_conversation' => $this->id_conversation]);
    }

    public function getMessages(int $limit = 50, int $offset = 0): array
    {
        if (!isset($this->id_conversation)) return [];
        $messageModel = new MessageChat($this->db);
        return $messageModel->trouverParCritere(['id_conversation' => $this->id_conversation], ['*'], 'AND', 'date_envoi DESC', $limit, $offset);
    }

    public function getDernierMessage(): ?array
    {
        $messages = $this->getMessages(1);
        return $messages[0] ?? null;
    }
}