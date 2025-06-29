<?php
namespace App\Backend\Model;

use PDO;

class VoteCommission extends BaseModel
{
    public string $table = 'vote_commission';
    public string|array $primaryKey = 'id_vote';

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }

    /**
     * Trouve un vote unique pour un rapport, un enseignant et un tour de vote.
     * @param string $idRapportEtudiant
     * @param string $numeroEnseignant
     * @param int $tourVote
     * @return array|null
     */
    public function trouverVoteUnique(string $idRapportEtudiant, string $numeroEnseignant, int $tourVote): ?array
    {
        return $this->trouverUnParCritere([
            'id_rapport_etudiant' => $idRapportEtudiant,
            'numero_enseignant' => $numeroEnseignant,
            'tour_vote' => $tourVote
        ]);
    }
}