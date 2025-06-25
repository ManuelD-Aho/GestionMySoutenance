<?php
namespace App\Backend\Model;

use PDO;

class RapportModeleAssignation extends BaseModel
{
    protected string $table = 'rapport_modele_assignation';
    protected string|array $primaryKey = ['id_modele', 'id_niveau_etude']; // Clé primaire composite

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }

    /**
     * Liste les niveaux d'étude auxquels un modèle de rapport est assigné.
     * @param string $idModele L'ID du modèle de rapport.
     * @param array $colonnes Les colonnes à sélectionner.
     * @return array Liste des assignations.
     */
    public function trouverAssignationsParModele(string $idModele, array $colonnes = ['*']): array
    {
        return $this->trouverParCritere(['id_modele' => $idModele], $colonnes);
    }

    /**
     * Liste les modèles de rapports assignés à un niveau d'étude donné.
     * @param string $idNiveauEtude L'ID du niveau d'étude.
     * @param array $colonnes Les colonnes à sélectionner.
     * @return array Liste des assignations.
     */
    public function trouverModelesAssignesANiveau(string $idNiveauEtude, array $colonnes = ['*']): array
    {
        return $this->trouverParCritere(['id_niveau_etude' => $idNiveauEtude], $colonnes);
    }
}