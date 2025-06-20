<?php
namespace App\Backend\Model;

use PDO;

class Attribuer extends BaseModel
{
    protected string $table = 'attribuer';
    protected string|array $primaryKey = ['numero_enseignant', 'id_specialite']; // Clé composite

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }

    /**
     * Trouve une attribution spécifique par ses clés composées.
     * @param string $numeroEnseignant Le numéro d'enseignant.
     * @param string $idSpecialite L'ID de la spécialité.
     * @param array $colonnes Les colonnes à sélectionner.
     * @return array|null Les données de l'attribution ou null si non trouvée.
     */
    public function trouverAttributionParCles(string $numeroEnseignant, string $idSpecialite, array $colonnes = ['*']): ?array
    {
        return $this->trouverUnParCritere([
            'numero_enseignant' => $numeroEnseignant,
            'id_specialite' => $idSpecialite
        ], $colonnes);
    }

    /**
     * Met à jour une attribution spécifique par ses clés composées.
     * @param string $numeroEnseignant Le numéro d'enseignant.
     * @param string $idSpecialite L'ID de la spécialité.
     * @param array $donnees Les données à mettre à jour.
     * @return bool Vrai si la mise à jour a réussi, faux sinon.
     */
    public function mettreAJourAttributionParCles(string $numeroEnseignant, string $idSpecialite, array $donnees): bool
    {
        return $this->mettreAJourParClesInternes(['numero_enseignant' => $numeroEnseignant, 'id_specialite' => $idSpecialite], $donnees);
    }

    /**
     * Supprime une attribution spécifique par ses clés composées.
     * @param string $numeroEnseignant Le numéro d'enseignant.
     * @param string $idSpecialite L'ID de la spécialité.
     * @return bool Vrai si la suppression a réussi, faux sinon.
     */
    public function supprimerAttributionParCles(string $numeroEnseignant, string $idSpecialite): bool
    {
        return $this->supprimerParClesInternes(['numero_enseignant' => $numeroEnseignant, 'id_specialite' => $idSpecialite]);
    }
}