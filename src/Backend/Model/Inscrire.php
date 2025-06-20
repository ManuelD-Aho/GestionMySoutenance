<?php
namespace App\Backend\Model;

use PDO;

class Inscrire extends BaseModel
{
    protected string $table = 'inscrire';
    // La clé primaire est composite et contient des VARCHAR (string en PHP)
    protected string|array $primaryKey = ['numero_carte_etudiant', 'id_niveau_etude', 'id_annee_academique'];

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }

    /**
     * Trouve une inscription spécifique par ses clés composées.
     * @param string $numeroCarteEtudiant Le numéro de carte de l'étudiant.
     * @param string $idNiveauEtude L'ID du niveau d'étude.
     * @param string $idAnneeAcademique L'ID de l'année académique.
     * @param array $colonnes Les colonnes à sélectionner.
     * @return array|null Les données de l'inscription ou null si non trouvée.
     */
    public function trouverParCleComposite(string $numeroCarteEtudiant, string $idNiveauEtude, string $idAnneeAcademique, array $colonnes = ['*']): ?array
    {
        return $this->trouverUnParCritere([
            'numero_carte_etudiant' => $numeroCarteEtudiant,
            'id_niveau_etude' => $idNiveauEtude,
            'id_annee_academique' => $idAnneeAcademique
        ], $colonnes);
    }

    /**
     * Met à jour une inscription spécifique par ses clés composées.
     * @param string $numeroCarteEtudiant Le numéro de carte de l'étudiant.
     * @param string $idNiveauEtude L'ID du niveau d'étude.
     * @param string $idAnneeAcademique L'ID de l'année académique.
     * @param array $donnees Les données à mettre à jour.
     * @return bool Vrai si la mise à jour a réussi, faux sinon.
     */
    public function mettreAJourParCleComposite(string $numeroCarteEtudiant, string $idNiveauEtude, string $idAnneeAcademique, array $donnees): bool
    {
        return $this->mettreAJourParClesInternes([
            'numero_carte_etudiant' => $numeroCarteEtudiant,
            'id_niveau_etude' => $idNiveauEtude,
            'id_annee_academique' => $idAnneeAcademique
        ], $donnees);
    }

    /**
     * Supprime une inscription spécifique par ses clés composées.
     * @param string $numeroCarteEtudiant Le numéro de carte de l'étudiant.
     * @param string $idNiveauEtude L'ID du niveau d'étude.
     * @param string $idAnneeAcademique L'ID de l'année académique.
     * @return bool Vrai si la suppression a réussi, faux sinon.
     */
    public function supprimerParCleComposite(string $numeroCarteEtudiant, string $idNiveauEtude, string $idAnneeAcademique): bool
    {
        return $this->supprimerParClesInternes([
            'numero_carte_etudiant' => $numeroCarteEtudiant,
            'id_niveau_etude' => $idNiveauEtude,
            'id_annee_academique' => $idAnneeAcademique
        ]);
    }
}