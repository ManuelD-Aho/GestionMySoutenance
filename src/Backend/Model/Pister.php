<?php
namespace App\Backend\Model;

use PDO;

class Pister extends BaseModel
{
    protected string $table = 'pister';
    // La clé primaire est composite et contient des VARCHAR/DATETIME (string en PHP)
    protected string|array $primaryKey = ['numero_utilisateur', 'id_traitement', 'date_pister'];

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }

    /**
     * Trouve une piste d'accès spécifique par ses clés composées.
     * @param string $numeroUtilisateur Le numéro de l'utilisateur.
     * @param string $idTraitement L'ID du traitement.
     * @param string $datePister La date et heure de la piste (format YYYY-MM-DD HH:MM:SS).
     * @param array $colonnes Les colonnes à sélectionner.
     * @return array|null Les données de la piste ou null si non trouvée.
     */
    public function trouverPisteParCles(string $numeroUtilisateur, string $idTraitement, string $datePister, array $colonnes = ['*']): ?array
    {
        return $this->trouverUnParCritere([
            'numero_utilisateur' => $numeroUtilisateur,
            'id_traitement' => $idTraitement,
            'date_pister' => $datePister
        ], $colonnes);
    }

    /**
     * Met à jour une piste d'accès spécifique par ses clés composées.
     * @param string $numeroUtilisateur Le numéro de l'utilisateur.
     * @param string $idTraitement L'ID du traitement.
     * @param string $datePister La date et heure de la piste.
     * @param array $donnees Les données à mettre à jour.
     * @return bool Vrai si la mise à jour a réussi, faux sinon.
     */
    public function mettreAJourPisteParCles(string $numeroUtilisateur, string $idTraitement, string $datePister, array $donnees): bool
    {
        return $this->mettreAJourParClesInternes([
            'numero_utilisateur' => $numeroUtilisateur,
            'id_traitement' => $idTraitement,
            'date_pister' => $datePister
        ], $donnees);
    }

    /**
     * Supprime une piste d'accès spécifique par ses clés composées.
     * @param string $numeroUtilisateur Le numéro de l'utilisateur.
     * @param string $idTraitement L'ID du traitement.
     * @param string $datePister La date et heure de la piste.
     * @return bool Vrai si la suppression a réussi, faux sinon.
     */
    public function supprimerPisteParCles(string $numeroUtilisateur, string $idTraitement, string $datePister): bool
    {
        return $this->supprimerParClesInternes([
            'numero_utilisateur' => $numeroUtilisateur,
            'id_traitement' => $idTraitement,
            'date_pister' => $datePister
        ]);
    }
}