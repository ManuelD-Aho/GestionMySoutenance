<?php
namespace App\Backend\Model;

use PDO;

class Affecter extends BaseModel
{
    protected string $table = 'affecter';
    protected string|array $primaryKey = ['numero_enseignant', 'id_rapport_etudiant', 'id_statut_jury']; // Clé composite

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }

    /**
     * Trouve une affectation spécifique par ses clés composées.
     * @param string $numeroEnseignant Le numéro d'enseignant.
     * @param string $idRapportEtudiant L'ID du rapport étudiant.
     * @param string $idStatutJury L'ID du statut du jury.
     * @param array $colonnes Les colonnes à sélectionner.
     * @return array|null Les données de l'affectation ou null si non trouvée.
     */
    public function trouverAffectationParCles(string $numeroEnseignant, string $idRapportEtudiant, string $idStatutJury, array $colonnes = ['*']): ?array
    {
        return $this->trouverUnParCritere([
            'numero_enseignant' => $numeroEnseignant,
            'id_rapport_etudiant' => $idRapportEtudiant,
            'id_statut_jury' => $idStatutJury
        ], $colonnes);
    }

    /**
     * Met à jour une affectation spécifique par ses clés composées.
     * @param string $numeroEnseignant Le numéro d'enseignant.
     * @param string $idRapportEtudiant L'ID du rapport étudiant.
     * @param string $idStatutJury L'ID du statut du jury.
     * @param array $donnees Les données à mettre à jour.
     * @return bool Vrai si la mise à jour a réussi, faux sinon.
     */
    public function mettreAJourAffectationParCles(string $numeroEnseignant, string $idRapportEtudiant, string $idStatutJury, array $donnees): bool
    {
        return $this->mettreAJourParClesInternes(['numero_enseignant' => $numeroEnseignant, 'id_rapport_etudiant' => $idRapportEtudiant, 'id_statut_jury' => $idStatutJury], $donnees);
    }

    /**
     * Supprime une affectation spécifique par ses clés composées.
     * @param string $numeroEnseignant Le numéro d'enseignant.
     * @param string $idRapportEtudiant L'ID du rapport étudiant.
     * @param string $idStatutJury L'ID du statut du jury.
     * @return bool Vrai si la suppression a réussi, faux sinon.
     */
    public function supprimerAffectationParCles(string $numeroEnseignant, string $idRapportEtudiant, string $idStatutJury): bool
    {
        return $this->supprimerParClesInternes(['numero_enseignant' => $numeroEnseignant, 'id_rapport_etudiant' => $idRapportEtudiant, 'id_statut_jury' => $idStatutJury]);
    }
}