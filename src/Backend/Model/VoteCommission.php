<?php
namespace App\Backend\Model;

use PDO;

class VoteCommission extends BaseModel
{
    protected string $table = 'vote_commission';
    protected string|array $primaryKey = 'id_vote'; // Clé primaire de type string

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }

    /**
     * Trouve les votes liés à une session spécifique.
     * @param string $idSession L'ID de la session de validation.
     * @param array $colonnes Les colonnes à sélectionner.
     * @return array Liste des votes pour la session.
     */
    public function trouverVotesParSession(string $idSession, array $colonnes = ['*']): array
    {
        return $this->trouverParCritere(['id_session' => $idSession], $colonnes);
    }

    /**
     * Trouve un vote spécifique par rapport, enseignant et tour de vote.
     * Utile pour vérifier si un enseignant a déjà voté pour un rapport donné et un tour.
     * @param string $idRapportEtudiant L'ID du rapport étudiant.
     * @param string $numeroEnseignant Le numéro de l'enseignant.
     * @param int $tourVote Le tour de vote actuel.
     * @param array $colonnes Les colonnes à sélectionner.
     * @return array|null Les données du vote ou null si non trouvé.
     */
    public function trouverVoteUnique(string $idRapportEtudiant, string $numeroEnseignant, int $tourVote, array $colonnes = ['*']): ?array
    {
        return $this->trouverUnParCritere([
            'id_rapport_etudiant' => $idRapportEtudiant,
            'numero_enseignant' => $numeroEnseignant,
            'tour_vote' => $tourVote
        ], $colonnes);
    }
}