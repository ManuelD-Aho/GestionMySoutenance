<?php
namespace App\Backend\Model;

use PDO;

class Evaluer extends BaseModel
{
    protected string $table = 'evaluer';
    // Nouvelle clé primaire composite après la modification de la table evaluer
    protected string|array $primaryKey = ['numero_carte_etudiant', 'id_ecue'];

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }

    /**
     * Trouve une évaluation spécifique par les clés composées (étudiant et ECUE).
     * @param string $numeroCarteEtudiant Le numéro de carte de l'étudiant.
     * @param string $idEcue L'ID de l'ECUE.
     * @param array $colonnes Les colonnes à sélectionner.
     * @return array|null Les données de l'évaluation ou null si non trouvée.
     */
    public function trouverEvaluationParCles(string $numeroCarteEtudiant, string $idEcue, array $colonnes = ['*']): ?array
    {
        return $this->trouverUnParCritere([
            'numero_carte_etudiant' => $numeroCarteEtudiant,
            'id_ecue' => $idEcue
        ], $colonnes);
    }

    /**
     * Met à jour une évaluation spécifique par ses clés composées.
     * @param string $numeroCarteEtudiant Le numéro de carte de l'étudiant.
     * @param string $idEcue L'ID de l'ECUE.
     * @param array $donnees Les données à mettre à jour.
     * @return bool Vrai si la mise à jour a réussi, faux sinon.
     */
    public function mettreAJourEvaluationParCles(string $numeroCarteEtudiant, string $idEcue, array $donnees): bool
    {
        return $this->mettreAJourParClesInternes([
            'numero_carte_etudiant' => $numeroCarteEtudiant,
            'id_ecue' => $idEcue
        ], $donnees);
    }

    /**
     * Supprime une évaluation spécifique par ses clés composées.
     * @param string $numeroCarteEtudiant Le numéro de carte de l'étudiant.
     * @param string $idEcue L'ID de l'ECUE.
     * @return bool Vrai si la suppression a réussi, faux sinon.
     */
    public function supprimerEvaluationParCles(string $numeroCarteEtudiant, string $idEcue): bool
    {
        return $this->supprimerParClesInternes([
            'numero_carte_etudiant' => $numeroCarteEtudiant,
            'id_ecue' => $idEcue
        ]);
    }
}