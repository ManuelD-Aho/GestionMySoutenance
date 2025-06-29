<?php
namespace App\Backend\Model;

use PDO;

class ParticipantConversation extends BaseModel
{
    public string $table = 'participant_conversation';
    public string|array $primaryKey = ['id_conversation', 'numero_utilisateur'];

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }

    /**
     * Trouve un participant par ses clés.
     * @param string $idConversation
     * @param string $numeroUtilisateur
     * @return array|null
     */
    public function trouverParticipantParCles(string $idConversation, string $numeroUtilisateur): ?array
    {
        return $this->trouverUnParCritere([
            'id_conversation' => $idConversation,
            'numero_utilisateur' => $numeroUtilisateur
        ]);
    }
}