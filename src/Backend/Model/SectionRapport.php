<?php
namespace App\Backend\Model;

use PDO;

class SectionRapport extends BaseModel
{
    protected string $table = 'section_rapport';
    // Clé primaire composite: id_rapport_etudiant et titre_section (selon la correction DDL)
    protected string|array $primaryKey = ['id_rapport_etudiant', 'titre_section']; // CORRECTION APPLIQUÉE

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }

    /**
     * Trouve toutes les sections d'un rapport donné, ordonnées pour l'affichage.
     * @param string $idRapportEtudiant L'ID du rapport étudiant.
     * @param array $colonnes Les colonnes à sélectionner.
     * @return array Liste des sections du rapport, triées par `ordre`.
     */
    public function trouverSectionsPourRapport(string $idRapportEtudiant, array $colonnes = ['*']): array
    {
        return $this->trouverParCritere(['id_rapport_etudiant' => $idRapportEtudiant], $colonnes, 'AND', 'ordre ASC');
    }

    /**
     * Trouve une section spécifique d'un rapport par son titre.
     * @param string $idRapportEtudiant L'ID du rapport étudiant.
     * @param string $titreSection Le titre de la section (ex: 'Introduction', 'Résumé', 'Conclusion').
     * @param array $colonnes Les colonnes à sélectionner.
     * @return array|null La section trouvée ou null.
     */
    public function trouverSectionUnique(string $idRapportEtudiant, string $titreSection, array $colonnes = ['*']): ?array
    {
        return $this->trouverUnParCritere([
            'id_rapport_etudiant' => $idRapportEtudiant,
            'titre_section' => $titreSection
        ], $colonnes);
    }
}