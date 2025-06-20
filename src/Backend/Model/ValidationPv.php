<?php
namespace App\Backend\Model;

use PDO;

class ValidationPv extends BaseModel
{
    protected string $table = 'validation_pv';
    // La clé primaire est composite et contient des VARCHAR (string en PHP)
    protected string|array $primaryKey = ['id_compte_rendu', 'numero_enseignant'];

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }

    /**
     * Trouve une validation de PV spécifique par ses clés composées.
     * @param string $idCompteRendu L'ID du compte rendu (PV).
     * @param string $numeroEnseignant Le numéro d'enseignant.
     * @param array $colonnes Les colonnes à sélectionner.
     * @return array|null Les données de la validation de PV ou null si non trouvée.
     */
    public function trouverValidationPvParCles(string $idCompteRendu, string $numeroEnseignant, array $colonnes = ['*']): ?array
    {
        return $this->trouverUnParCritere([
            'id_compte_rendu' => $idCompteRendu,
            'numero_enseignant' => $numeroEnseignant
        ], $colonnes);
    }

    /**
     * Met à jour une validation de PV spécifique par ses clés composées.
     * @param string $idCompteRendu L'ID du compte rendu (PV).
     * @param string $numeroEnseignant Le numéro d'enseignant.
     * @param array $donnees Les données à mettre à jour.
     * @return bool Vrai si la mise à jour a réussi, faux sinon.
     */
    public function mettreAJourValidationPvParCles(string $idCompteRendu, string $numeroEnseignant, array $donnees): bool
    {
        return $this->mettreAJourParClesInternes(['id_compte_rendu' => $idCompteRendu, 'numero_enseignant' => $numeroEnseignant], $donnees);
    }

    /**
     * Supprime une validation de PV spécifique par ses clés composées.
     * @param string $idCompteRendu L'ID du compte rendu (PV).
     * @param string $numeroEnseignant Le numéro d'enseignant.
     * @return bool Vrai si la suppression a réussi, faux sinon.
     */
    public function supprimerValidationPvParCles(string $idCompteRendu, string $numeroEnseignant): bool
    {
        return $this->supprimerParClesInternes(['id_compte_rendu' => $idCompteRendu, 'numero_enseignant' => $numeroEnseignant]);
    }
}