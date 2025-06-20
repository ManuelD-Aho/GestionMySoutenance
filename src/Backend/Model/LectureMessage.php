<?php
namespace App\Backend\Model;

use PDO;

class LectureMessage extends BaseModel
{
    protected string $table = 'lecture_message';
    // La clé primaire est composite et contient des VARCHAR (string en PHP)
    protected string|array $primaryKey = ['id_message_chat', 'numero_utilisateur'];

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }

    /**
     * Trouve une entrée de lecture de message spécifique par ses clés composées.
     * @param string $idMessageChat L'ID du message de chat.
     * @param string $numeroUtilisateur Le numéro de l'utilisateur.
     * @param array $colonnes Les colonnes à sélectionner.
     * @return array|null Les données de lecture du message ou null si non trouvée.
     */
    public function trouverLectureParCles(string $idMessageChat, string $numeroUtilisateur, array $colonnes = ['*']): ?array
    {
        return $this->trouverUnParCritere([
            'id_message_chat' => $idMessageChat,
            'numero_utilisateur' => $numeroUtilisateur
        ], $colonnes);
    }

    /**
     * Met à jour une entrée de lecture de message spécifique par ses clés composées.
     * @param string $idMessageChat L'ID du message de chat.
     * @param string $numeroUtilisateur Le numéro de l'utilisateur.
     * @param array $donnees Les données à mettre à jour.
     * @return bool Vrai si la mise à jour a réussi, faux sinon.
     */
    public function mettreAJourLectureParCles(string $idMessageChat, string $numeroUtilisateur, array $donnees): bool
    {
        return $this->mettreAJourParClesInternes([
            'id_message_chat' => $idMessageChat,
            'numero_utilisateur' => $numeroUtilisateur
        ], $donnees);
    }

    /**
     * Supprime une entrée de lecture de message spécifique par ses clés composées.
     * @param string $idMessageChat L'ID du message de chat.
     * @param string $numeroUtilisateur Le numéro de l'utilisateur.
     * @return bool Vrai si la suppression a réussi, faux sinon.
     */
    public function supprimerLectureParCles(string $idMessageChat, string $numeroUtilisateur): bool
    {
        return $this->supprimerParClesInternes([
            'id_message_chat' => $idMessageChat,
            'numero_utilisateur' => $numeroUtilisateur
        ]);
    }
}