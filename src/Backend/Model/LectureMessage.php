<?php
namespace App\Backend\Model;

use PDO;

class LectureMessage extends BaseModel
{
    public string $table = 'lecture_message';
    public string|array $primaryKey = ['id_message_chat', 'numero_utilisateur'];

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }

    /**
     * Trouve un enregistrement de lecture par ses clés.
     * @param string $idMessageChat
     * @param string $numeroUtilisateur
     * @return array|null
     */
    public function trouverLectureParCles(string $idMessageChat, string $numeroUtilisateur): ?array
    {
        return $this->trouverUnParCritere([
            'id_message_chat' => $idMessageChat,
            'numero_utilisateur' => $numeroUtilisateur
        ]);
    }
}