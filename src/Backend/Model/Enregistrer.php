<?php
namespace App\Backend\Model;

use PDO;

class Enregistrer extends BaseModel
{
    protected string $table = 'enregistrer';
    // La clé primaire est composite et contient des VARCHAR/DATETIME (string en PHP)
    protected string|array $primaryKey = ['numero_utilisateur', 'id_action', 'date_action'];

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }

    /**
     * Trouve un enregistrement d'action par ses clés composées.
     * @param string $numeroUtilisateur Le numéro de l'utilisateur concerné.
     * @param string $idAction L'ID de l'action enregistrée.
     * @param string $dateAction La date et heure de l'action (format YYYY-MM-DD HH:MM:SS).
     * @param array $colonnes Les colonnes à sélectionner.
     * @return array|null Les données de l'enregistrement ou null si non trouvé.
     */
    public function trouverEnregistrementParCles(string $numeroUtilisateur, string $idAction, string $dateAction, array $colonnes = ['*']): ?array
    {
        return $this->trouverUnParCritere([
            'numero_utilisateur' => $numeroUtilisateur,
            'id_action' => $idAction,
            'date_action' => $dateAction
        ], $colonnes);
    }

    /**
     * Met à jour un enregistrement d'action par ses clés composées.
     * @param string $numeroUtilisateur Le numéro de l'utilisateur.
     * @param string $idAction L'ID de l'action.
     * @param string $dateAction La date et heure de l'action.
     * @param array $donnees Les données à mettre à jour.
     * @return bool Vrai si la mise à jour a réussi, faux sinon.
     */
    public function mettreAJourEnregistrementParCles(string $numeroUtilisateur, string $idAction, string $dateAction, array $donnees): bool
    {
        return $this->mettreAJourParClesInternes([
            'numero_utilisateur' => $numeroUtilisateur,
            'id_action' => $idAction,
            'date_action' => $dateAction
        ], $donnees);
    }

    /**
     * Supprime un enregistrement d'action par ses clés composées.
     * @param string $numeroUtilisateur Le numéro de l'utilisateur.
     * @param string $idAction L'ID de l'action.
     * @param string $dateAction La date et heure de l'action.
     * @return bool Vrai si la suppression a réussi, faux sinon.
     */
    public function supprimerEnregistrementParCles(string $numeroUtilisateur, string $idAction, string $dateAction): bool
    {
        return $this->supprimerParClesInternes([
            'numero_utilisateur' => $numeroUtilisateur,
            'id_action' => $idAction,
            'date_action' => $dateAction
        ]);
    }
}