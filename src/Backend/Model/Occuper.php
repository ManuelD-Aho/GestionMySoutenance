<?php
namespace App\Backend\Model;

use PDO;

class Occuper extends BaseModel
{
    protected string $table = 'occuper';
    // La clé primaire est composite et contient des VARCHAR (string en PHP)
    protected string|array $primaryKey = ['id_fonction', 'numero_enseignant'];

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }

    /**
     * Trouve une occupation spécifique par ses clés composées (fonction et enseignant).
     * @param string $idFonction L'ID de la fonction.
     * @param string $numeroEnseignant Le numéro d'enseignant.
     * @param array $colonnes Les colonnes à sélectionner.
     * @return array|null Les données de l'occupation ou null si non trouvée.
     */
    public function trouverOccupationParCles(string $idFonction, string $numeroEnseignant, array $colonnes = ['*']): ?array
    {
        return $this->trouverUnParCritere([
            'id_fonction' => $idFonction,
            'numero_enseignant' => $numeroEnseignant
        ], $colonnes);
    }

    /**
     * Met à jour une occupation spécifique par ses clés composées.
     * @param string $idFonction L'ID de la fonction.
     * @param string $numeroEnseignant Le numéro d'enseignant.
     * @param array $donnees Les données à mettre à jour.
     * @return bool Vrai si la mise à jour a réussi, faux sinon.
     */
    public function mettreAJourOccupationParCles(string $idFonction, string $numeroEnseignant, array $donnees): bool
    {
        return $this->mettreAJourParClesInternes([
            'id_fonction' => $idFonction,
            'numero_enseignant' => $numeroEnseignant
        ], $donnees);
    }

    /**
     * Supprime une occupation spécifique par ses clés composées.
     * @param string $idFonction L'ID de la fonction.
     * @param string $numeroEnseignant Le numéro d'enseignant.
     * @return bool Vrai si la suppression a réussi, faux sinon.
     */
    public function supprimerOccupationParCles(string $idFonction, string $numeroEnseignant): bool
    {
        return $this->supprimerParClesInternes([
            'id_fonction' => $idFonction,
            'numero_enseignant' => $numeroEnseignant
        ]);
    }
}