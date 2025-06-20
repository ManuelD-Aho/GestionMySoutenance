<?php
namespace App\Backend\Model;

use PDO;

class ParticipantConversation extends BaseModel
{
    protected string $table = 'participant_conversation';
    // La clé primaire est composite et contient des VARCHAR (string en PHP)
    protected string|array $primaryKey = ['id_conversation', 'numero_utilisateur'];

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }

    /**
     * Trouve un participant à une conversation spécifique par ses clés composées.
     * @param string $idConversation L'ID de la conversation.
     * @param string $numeroUtilisateur Le numéro de l'utilisateur.
     * @param array $colonnes Les colonnes à sélectionner.
     * @return array|null Les données du participant ou null si non trouvé.
     */
    public function trouverParticipantParCles(string $idConversation, string $numeroUtilisateur, array $colonnes = ['*']): ?array
    {
        return $this->trouverUnParCritere([
            'id_conversation' => $idConversation,
            'numero_utilisateur' => $numeroUtilisateur
        ], $colonnes);
    }

    // Remarque: La méthode mettreAJourParticipantParCles n'était pas présente mais peut être ajoutée si nécessaire.
    // public function mettreAJourParticipantParCles(string $idConversation, string $numeroUtilisateur, array $donnees): bool
    // {
    //     return $this->mettreAJourParClesInternes(['id_conversation' => $idConversation, 'numero_utilisateur' => $numeroUtilisateur], $donnees);
    // }


    /**
     * Supprime un participant à une conversation spécifique par ses clés composées.
     * @param string $idConversation L'ID de la conversation.
     * @param string $numeroUtilisateur Le numéro de l'utilisateur.
     * @return bool Vrai si la suppression a réussi, faux sinon.
     */
    public function supprimerParticipantParCles(string $idConversation, string $numeroUtilisateur): bool
    {
        return $this->supprimerParClesInternes([
            'id_conversation' => $idConversation,
            'numero_utilisateur' => $numeroUtilisateur
        ]);
    }
}