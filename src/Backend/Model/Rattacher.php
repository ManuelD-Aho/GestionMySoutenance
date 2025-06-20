<?php
namespace App\Backend\Model;

use PDO;

class Rattacher extends BaseModel
{
    protected string $table = 'rattacher';
    // La clé primaire est composite et contient des VARCHAR (string en PHP)
    protected string|array $primaryKey = ['id_groupe_utilisateur', 'id_traitement'];

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }

    /**
     * Trouve un rattachement spécifique par ses clés composées (groupe utilisateur et traitement).
     * @param string $idGroupeUtilisateur L'ID du groupe d'utilisateurs.
     * @param string $idTraitement L'ID du traitement (permission).
     * @param array $colonnes Les colonnes à sélectionner.
     * @return array|null Les données du rattachement ou null si non trouvé.
     */
    public function trouverRattachementParCles(string $idGroupeUtilisateur, string $idTraitement, array $colonnes = ['*']): ?array
    {
        return $this->trouverUnParCritere([
            'id_groupe_utilisateur' => $idGroupeUtilisateur,
            'id_traitement' => $idTraitement
        ], $colonnes);
    }

    /**
     * Supprime un rattachement spécifique par ses clés composées.
     * @param string $idGroupeUtilisateur L'ID du groupe d'utilisateurs.
     * @param string $idTraitement L'ID du traitement (permission).
     * @return bool Vrai si la suppression a réussi, faux sinon.
     */
    public function supprimerRattachementParCles(string $idGroupeUtilisateur, string $idTraitement): bool
    {
        return $this->supprimerParClesInternes([
            'id_groupe_utilisateur' => $idGroupeUtilisateur,
            'id_traitement' => $idTraitement
        ]);
    }
    // La méthode mettreAJourRattachementParCles n'était pas présente mais peut être ajoutée si nécessaire.
    // public function mettreAJourRattachementParCles(string $idGroupeUtilisateur, string $idTraitement, array $donnees): bool
    // {
    //     return $this->mettreAJourParClesInternes(['id_groupe_utilisateur' => $idGroupeUtilisateur, 'id_traitement' => $idTraitement], $donnees);
    // }
}