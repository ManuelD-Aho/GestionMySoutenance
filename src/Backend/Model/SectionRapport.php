<?php
namespace App\Backend\Model;

use PDO;

class SectionRapport extends BaseModel
{
    protected string $table = 'section_rapport';
    // La clé primaire est composite: id_rapport_etudiant (VARCHAR(50)) et nom_section (VARCHAR(100))
    protected string|array $primaryKey = ['id_rapport_etudiant', 'nom_section'];

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }

    /**
     * Trouve toutes les sections d'un rapport donné, ordonnées pour l'affichage.
     * @param string $idRapportEtudiant L'ID du rapport étudiant.
     * @param array $colonnes Les colonnes à sélectionner.
     * @return array Liste des sections du rapport, triées par `ordre_affichage`.
     */
    public function trouverSectionsPourRapport(string $idRapportEtudiant, array $colonnes = ['*']): array
    {
        return $this->trouverParCritere(['id_rapport_etudiant' => $idRapportEtudiant], $colonnes, 'AND', 'ordre_affichage ASC');
    }

    /**
     * Trouve une section spécifique d'un rapport par son nom.
     * @param string $idRapportEtudiant L'ID du rapport étudiant.
     * @param string $nomSection Le nom de la section (ex: 'Introduction', 'Resume', 'Conclusion').
     * @param array $colonnes Les colonnes à sélectionner.
     * @return array|null La section trouvée ou null.
     */
    public function trouverSectionUnique(string $idRapportEtudiant, string $nomSection, array $colonnes = ['*']): ?array
    {
        return $this->trouverUnParCritere([
            'id_rapport_etudiant' => $idRapportEtudiant,
            'nom_section' => $nomSection
        ], $colonnes);
    }
}