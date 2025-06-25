<?php
namespace App\Backend\Model;

use PDO;

class ConformiteRapportDetails extends BaseModel
{
    protected string $table = 'conformite_rapport_details';
    protected string|array $primaryKey = 'id_conformite_detail'; // Clé primaire simple

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }

    /**
     * Trouve un détail de conformité spécifique par rapport et critère.
     * @param string $idRapportEtudiant L'ID du rapport étudiant.
     * @param string $idCritere L'ID du critère de conformité.
     * @param array $colonnes Les colonnes à sélectionner.
     * @return array|null Les données du détail ou null si non trouvé.
     */
    public function trouverDetailsParRapportEtCritere(string $idRapportEtudiant, string $idCritere, array $colonnes = ['*']): ?array
    {
        return $this->trouverUnParCritere([
            'id_rapport_etudiant' => $idRapportEtudiant,
            'id_critere' => $idCritere
        ], $colonnes);
    }

    /**
     * Liste tous les détails de conformité pour un rapport donné.
     * @param string $idRapportEtudiant L'ID du rapport étudiant.
     * @param array $colonnes Les colonnes à sélectionner.
     * @return array Liste des détails de conformité.
     */
    public function trouverDetailsParRapport(string $idRapportEtudiant, array $colonnes = ['*']): array
    {
        return $this->trouverParCritere(['id_rapport_etudiant' => $idRapportEtudiant], $colonnes);
    }

    /**
     * Compte le nombre de critères avec un statut de validation donné pour un rapport.
     * @param string $idRapportEtudiant L'ID du rapport étudiant.
     * @param string $statutValidation Le statut de validation (ex: 'Conforme', 'Non Conforme').
     * @return int Le nombre de critères correspondants.
     */
    public function compterDetailsParRapportEtStatut(string $idRapportEtudiant, string $statutValidation): int
    {
        return $this->compterParCritere([
            'id_rapport_etudiant' => $idRapportEtudiant,
            'statut_validation' => $statutValidation
        ]);
    }
}