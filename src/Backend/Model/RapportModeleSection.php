<?php
namespace App\Backend\Model;

use PDO;

class RapportModeleSection extends BaseModel
{
    protected string $table = 'rapport_modele_section';
    protected string|array $primaryKey = 'id_section_modele'; // Clé primaire simple

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }

    /**
     * Liste toutes les sections d'un modèle de rapport donné, ordonnées par leur ordre.
     * @param string $idModele L'ID du modèle de rapport.
     * @param array $colonnes Les colonnes à sélectionner.
     * @return array Liste des sections du modèle.
     */
    public function trouverSectionsParModele(string $idModele, array $colonnes = ['*']): array
    {
        return $this->trouverParCritere(['id_modele' => $idModele], $colonnes, 'AND', 'ordre ASC');
    }

    /**
     * Trouve une section spécifique d'un modèle de rapport par son titre.
     * @param string $idModele L'ID du modèle de rapport.
     * @param string $titreSection Le titre de la section.
     * @param array $colonnes Les colonnes à sélectionner.
     * @return array|null La section trouvée ou null.
     */
    public function trouverSectionModeleUnique(string $idModele, string $titreSection, array $colonnes = ['*']): ?array
    {
        return $this->trouverUnParCritere([
            'id_modele' => $idModele,
            'titre_section' => $titreSection
        ], $colonnes);
    }
}