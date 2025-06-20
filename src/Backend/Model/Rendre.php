<?php
namespace App\Backend\Model;

use PDO;

class Rendre extends BaseModel
{
    protected string $table = 'rendre';
    // La clé primaire est composite et contient des VARCHAR (string en PHP)
    protected string|array $primaryKey = ['numero_enseignant', 'id_compte_rendu'];

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }

    /**
     * Trouve une action de rendu spécifique par ses clés composées (enseignant et compte rendu).
     * @param string $numeroEnseignant Le numéro d'enseignant.
     * @param string $idCompteRendu L'ID du compte rendu (PV).
     * @param array $colonnes Les colonnes à sélectionner.
     * @return array|null Les données de l'action de rendu ou null si non trouvée.
     */
    public function trouverActionRenduParCles(string $numeroEnseignant, string $idCompteRendu, array $colonnes = ['*']): ?array
    {
        return $this->trouverUnParCritere([
            'numero_enseignant' => $numeroEnseignant,
            'id_compte_rendu' => $idCompteRendu
        ], $colonnes);
    }

    /**
     * Met à jour une action de rendu spécifique par ses clés composées.
     * @param string $numeroEnseignant Le numéro d'enseignant.
     * @param string $idCompteRendu L'ID du compte rendu (PV).
     * @param array $donnees Les données à mettre à jour.
     * @return bool Vrai si la mise à jour a réussi, faux sinon.
     */
    public function mettreAJourActionRenduParCles(string $numeroEnseignant, string $idCompteRendu, array $donnees): bool
    {
        return $this->mettreAJourParClesInternes(['numero_enseignant' => $numeroEnseignant, 'id_compte_rendu' => $idCompteRendu], $donnees);
    }

    /**
     * Supprime une action de rendu spécifique par ses clés composées.
     * @param string $numeroEnseignant Le numéro d'enseignant.
     * @param string $idCompteRendu L'ID du compte rendu (PV).
     * @return bool Vrai si la suppression a réussi, faux sinon.
     */
    public function supprimerActionRenduParCles(string $numeroEnseignant, string $idCompteRendu): bool
    {
        return $this->supprimerParClesInternes(['numero_enseignant' => $numeroEnseignant, 'id_compte_rendu' => $idCompteRendu]);
    }
}