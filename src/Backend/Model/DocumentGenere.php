<?php
namespace App\Backend\Model;

use PDO;

class DocumentGenere extends BaseModel
{
    protected string $table = 'document_genere';
    protected string|array $primaryKey = 'id_document_genere'; // Clé primaire VARCHAR(50)

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }

    /**
     * Trouve des documents générés liés à une entité source (ex: un rapport ou un PV).
     * @param string $idEntiteSource ID de l'entité source (ex: ID du rapport, ID du PV)
     * @param string $typeEntiteSource Type de l'entité source (ex: 'Rapport', 'PV')
     * @param array $colonnes Colonnes à sélectionner
     * @return array Liste des documents trouvés
     */
    public function trouverParEntiteSource(string $idEntiteSource, string $typeEntiteSource, array $colonnes = ['*']): array
    {
        return $this->trouverParCritere([
            'id_entite_source' => $idEntiteSource,
            'type_entite_source' => $typeEntiteSource
        ], $colonnes);
    }

    /**
     * Trouve des documents générés pour un utilisateur donné (ex: l'étudiant concerné par le PV).
     * @param string $numeroUtilisateurConcerne ID de l'utilisateur concerné
     * @param array $colonnes Colonnes à sélectionner
     * @return array Liste des documents trouvés
     */
    public function trouverParUtilisateurConcerne(string $numeroUtilisateurConcerne, array $colonnes = ['*']): array
    {
        return $this->trouverParCritere(['numero_utilisateur_concerne' => $numeroUtilisateurConcerne], $colonnes);
    }
}