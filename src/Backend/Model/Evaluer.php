<?php
namespace App\Backend\Model;

use PDO;

class Evaluer extends BaseModel
{
    protected string $table = 'evaluer';
    protected string|array $primaryKey = ['numero_carte_etudiant', 'id_ecue', 'id_annee_academique']; // CORRECTION APPLIQUÉE

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }

    /**
     * Trouve une évaluation spécifique par les clés composées (étudiant, ECUE et année académique).
     * @param string $numeroCarteEtudiant L'ID de l'étudiant.
     * @param string $idEcue L'ID de l'ECUE.
     * @param string $idAnneeAcademique L'ID de l'année académique.
     * @param array $colonnes Les colonnes à sélectionner.
     * @return array|null Les données de l'évaluation ou null si non trouvée.
     */
    public function trouverEvaluationParCles(string $numeroCarteEtudiant, string $idEcue, string $idAnneeAcademique, array $colonnes = ['*']): ?array
    {
        return $this->trouverUnParCritere([
            'numero_carte_etudiant' => $numeroCarteEtudiant,
            'id_ecue' => $idEcue,
            'id_annee_academique' => $idAnneeAcademique
        ], $colonnes);
    }

    /**
     * Met à jour une évaluation spécifique par ses clés composées.
     * @param string $numeroCarteEtudiant L'ID de l'étudiant.
     * @param string $idEcue L'ID de l'ECUE.
     * @param string $idAnneeAcademique L'ID de l'année académique.
     * @param array $donnees Les données à mettre à jour.
     * @return bool Vrai si la mise à jour a réussi, faux sinon.
     */
    public function mettreAJourEvaluationParCles(string $numeroCarteEtudiant, string $idEcue, string $idAnneeAcademique, array $donnees): bool
    {
        return $this->mettreAJourParClesInternes([
            'numero_carte_etudiant' => $numeroCarteEtudiant,
            'id_ecue' => $idEcue,
            'id_annee_academique' => $idAnneeAcademique
        ], $donnees);
    }

    /**
     * Supprime une évaluation spécifique par ses clés composées.
     * @param string $numeroCarteEtudiant L'ID de l'étudiant.
     * @param string $idEcue L'ID de l'ECUE.
     * @param string $idAnneeAcademique L'ID de l'année académique.
     * @return bool Vrai si la suppression a réussi, faux sinon.
     */
    public function supprimerEvaluationParCles(string $numeroCarteEtudiant, string $idEcue, string $idAnneeAcademique): bool
    {
        return $this->supprimerParClesInternes([
            'numero_carte_etudiant' => $numeroCarteEtudiant,
            'id_ecue' => $idEcue,
            'id_annee_academique' => $idAnneeAcademique
        ]);
    }
}