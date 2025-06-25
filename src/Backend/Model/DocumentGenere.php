<?php
namespace App\Backend\Model;

use PDO;

class DocumentGenere extends BaseModel
{
    protected string $table = 'document_genere';
    protected string|array $primaryKey = 'id_document_genere'; // CORRECTION APPLIQUÉE (renommage de la PK)

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }

    /**
     * Trouve des documents générés liés à une entité source (ex: un rapport ou un PV).
     * @param string $idEntiteConcernee ID de l'entité source (ex: ID du rapport, ID du PV)
     * @param string $typeEntiteConcernee Type de l'entité source (ex: 'Rapport', 'PV')
     * @param array $colonnes Colonnes à sélectionner
     * @return array Liste des documents trouvés
     */
    public function trouverParEntiteSource(string $idEntiteConcernee, string $typeEntiteConcernee, array $colonnes = ['*']): array
    {
        return $this->trouverParCritere([
            'id_entite_concernee' => $idEntiteConcernee,
            'type_entite_concernee' => $typeEntiteConcernee
        ], $colonnes);
    }

    /**
     * Trouve des documents générés pour un utilisateur donné.
     * NOTE: Cette méthode suppose l'existence de la colonne `numero_utilisateur_concerne` dans la DDL.
     * @param string $numeroUtilisateurConcerne ID de l'utilisateur concerné
     * @param array $colonnes Colonnes à sélectionner
     * @return array Liste des documents trouvés
     */
    public function trouverParUtilisateurConcerne(string $numeroUtilisateurConcerne, array $colonnes = ['*']): array
    {
        // CORRECTION APPLIQUÉE: La DDL est supposée avoir la colonne `numero_utilisateur_concerne`.
        return $this->trouverParCritere(['numero_utilisateur_concerne' => $numeroUtilisateurConcerne], $colonnes);
    }
}