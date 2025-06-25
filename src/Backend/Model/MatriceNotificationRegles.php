<?php
namespace App\Backend\Model;

use PDO;

class MatriceNotificationRegles extends BaseModel
{
    protected string $table = 'matrice_notification_regles';
    protected string|array $primaryKey = 'id_regle'; // Clé primaire simple

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }

    /**
     * Liste toutes les règles de notification pour un événement déclencheur donné.
     * @param string $idActionDeclencheur L'ID de l'action qui déclenche la notification.
     * @param array $colonnes Les colonnes à sélectionner.
     * @return array Liste des règles de notification.
     */
    public function trouverReglesParActionDeclencheur(string $idActionDeclencheur, array $colonnes = ['*']): array
    {
        return $this->trouverParCritere(['id_action_declencheur' => $idActionDeclencheur, 'est_active' => 1], $colonnes);
    }

    /**
     * Liste toutes les règles de notification pour un groupe destinataire donné.
     * @param string $idGroupeDestinataire L'ID du groupe destinataire.
     * @param array $colonnes Les colonnes à sélectionner.
     * @return array Liste des règles de notification.
     */
    public function trouverReglesParGroupeDestinataire(string $idGroupeDestinataire, array $colonnes = ['*']): array
    {
        return $this->trouverParCritere(['id_groupe_destinataire' => $idGroupeDestinataire, 'est_active' => 1], $colonnes);
    }
}