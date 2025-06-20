<?php
namespace App\Backend\Model;

use PDO;

class SessionValidation extends BaseModel
{
    protected string $table = 'session_validation';
    protected string|array $primaryKey = 'id_session'; // Clé primaire VARCHAR(50)

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }

    /**
     * Trouve les sessions de validation pour un président de commission donné.
     * @param string $numeroPresident Le numéro d'enseignant du président.
     * @param array $colonnes Les colonnes à sélectionner.
     * @return array Liste des sessions trouvées.
     */
    public function trouverSessionsParPresident(string $numeroPresident, array $colonnes = ['*']): array
    {
        return $this->trouverParCritere(['numero_president_commission' => $numeroPresident], $colonnes);
    }

    /**
     * Trouve les sessions avec un statut donné.
     * @param string $statut Le statut de la session (ex: 'Planifiee', 'En cours', 'Cloturee').
     * @param array $colonnes Les colonnes à sélectionner.
     * @return array Liste des sessions trouvées.
     */
    public function trouverSessionsParStatut(string $statut, array $colonnes = ['*']): array
    {
        return $this->trouverParCritere(['statut_session' => $statut], $colonnes);
    }
}