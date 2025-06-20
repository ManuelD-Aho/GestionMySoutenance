<?php
namespace App\Backend\Model;

use PDO;

/**
 * Modèle pour interagir avec la table 'sessions' gérée par DatabaseSessionHandler.
 * Les méthodes CRUD classiques de BaseModel peuvent être utilisées, mais les interactions principales
 * se feront via le DatabaseSessionHandler pour les opérations de session standard.
 */
class Sessions extends BaseModel
{
    protected string $table = 'sessions';
    protected string|array $primaryKey = 'session_id'; // Clé primaire VARCHAR(255)

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }

    /**
     * Trouve les sessions actives pour un utilisateur donné.
     * Cette méthode est utile pour la mise à jour des permissions en temps réel
     * lorsque les droits d'un groupe sont modifiés par un administrateur.
     * @param string $userId L'ID de l'utilisateur (numero_utilisateur).
     * @param array $colonnes Colonnes à sélectionner.
     * @return array Liste des sessions de l'utilisateur.
     */
    public function trouverSessionsParUtilisateur(string $userId, array $colonnes = ['*']): array
    {
        return $this->trouverParCritere(['user_id' => $userId], $colonnes);
    }
}
