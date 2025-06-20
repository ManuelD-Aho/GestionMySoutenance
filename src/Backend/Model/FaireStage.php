<?php
namespace App\Backend\Model;

use PDO;

class FaireStage extends BaseModel
{
    protected string $table = 'faire_stage';
    // La clé primaire est composite et contient des VARCHAR (string en PHP)
    protected string|array $primaryKey = ['id_entreprise', 'numero_carte_etudiant'];

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }

    /**
     * Trouve une entrée de stage spécifique par les clés composées (entreprise et étudiant).
     * @param string $idEntreprise L'ID de l'entreprise.
     * @param string $numeroCarteEtudiant Le numéro de carte de l'étudiant.
     * @param array $colonnes Les colonnes à sélectionner.
     * @return array|null Les données du stage ou null si non trouvé.
     */
    public function trouverStageParCles(string $idEntreprise, string $numeroCarteEtudiant, array $colonnes = ['*']): ?array
    {
        return $this->trouverUnParCritere([
            'id_entreprise' => $idEntreprise,
            'numero_carte_etudiant' => $numeroCarteEtudiant
        ], $colonnes);
    }

    /**
     * Met à jour une entrée de stage spécifique par ses clés composées.
     * @param string $idEntreprise L'ID de l'entreprise.
     * @param string $numeroCarteEtudiant Le numéro de carte de l'étudiant.
     * @param array $donnees Les données à mettre à jour.
     * @return bool Vrai si la mise à jour a réussi, faux sinon.
     */
    public function mettreAJourStageParCles(string $idEntreprise, string $numeroCarteEtudiant, array $donnees): bool
    {
        return $this->mettreAJourParClesInternes([
            'id_entreprise' => $idEntreprise,
            'numero_carte_etudiant' => $numeroCarteEtudiant
        ], $donnees);
    }

    /**
     * Supprime une entrée de stage spécifique par ses clés composées.
     * @param string $idEntreprise L'ID de l'entreprise.
     * @param string $numeroCarteEtudiant Le numéro de carte de l'étudiant.
     * @return bool Vrai si la suppression a réussi, faux sinon.
     */
    public function supprimerStageParCles(string $idEntreprise, string $numeroCarteEtudiant): bool
    {
        return $this->supprimerParClesInternes([
            'id_entreprise' => $idEntreprise,
            'numero_carte_etudiant' => $numeroCarteEtudiant
        ]);
    }
}