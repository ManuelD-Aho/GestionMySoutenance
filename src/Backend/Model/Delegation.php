<?php
namespace App\Backend\Model;

use PDO;

class Delegation extends BaseModel
{
    protected string $table = 'delegation';
    protected string|array $primaryKey = 'id_delegation'; // Clé primaire simple

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }

    /**
     * Liste toutes les délégations faites par un utilisateur.
     * @param string $idDelegant L'ID de l'utilisateur délégant.
     * @param array $colonnes Les colonnes à sélectionner.
     * @return array Liste des délégations.
     */
    public function trouverDelegationsParDelegant(string $idDelegant, array $colonnes = ['*']): array
    {
        return $this->trouverParCritere(['id_delegant' => $idDelegant], $colonnes);
    }

    /**
     * Liste toutes les délégations reçues par un utilisateur.
     * @param string $idDelegue L'ID de l'utilisateur délégué.
     * @param array $colonnes Les colonnes à sélectionner.
     * @return array Liste des délégations.
     */
    public function trouverDelegationsParDelegue(string $idDelegue, array $colonnes = ['*']): array
    {
        return $this->trouverParCritere(['id_delegue' => $idDelegue], $colonnes);
    }

    /**
     * Trouve une délégation active spécifique pour un délégué, un traitement et un contexte optionnel.
     * @param string $idDelegue L'ID de l'utilisateur délégué.
     * @param string $idTraitement L'ID du traitement délégué.
     * @param string|null $contexteId L'ID du contexte (ex: ID de session).
     * @param string|null $contexteType Le type du contexte (ex: 'Session').
     * @param array $colonnes Les colonnes à sélectionner.
     * @return array|null La délégation active ou null.
     */
    public function trouverDelegationActive(string $idDelegue, string $idTraitement, ?string $contexteId = null, ?string $contexteType = null, array $colonnes = ['*']): ?array
    {
        $criteres = [
            'id_delegue' => $idDelegue,
            'id_traitement' => $idTraitement,
            'statut' => 'Active',
            'date_debut' => ['operator' => '<=', 'value' => date('Y-m-d H:i:s')],
            'date_fin' => ['operator' => '>=', 'value' => date('Y-m-d H:i:s')]
        ];

        if ($contexteId !== null) {
            $criteres['contexte_id'] = $contexteId;
        }
        if ($contexteType !== null) {
            $criteres['contexte_type'] = $contexteType;
        }

        return $this->trouverUnParCritere($criteres);
    }
}