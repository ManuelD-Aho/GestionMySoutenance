<?php
namespace App\Backend\Model;

use PDO;

class Recevoir extends BaseModel
{
    protected string $table = 'recevoir';
    // La clé primaire est composite et contient des VARCHAR/DATETIME (string en PHP)
    protected string|array $primaryKey = ['numero_utilisateur', 'id_notification', 'date_reception'];

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }

    /**
     * Trouve une entrée de réception de notification spécifique par ses clés composées.
     * @param string $numeroUtilisateur Le numéro de l'utilisateur.
     * @param string $idNotification L'ID de la notification.
     * @param string $dateReception La date et heure de réception (format YYYY-MM-DD HH:MM:SS).
     * @param array $colonnes Les colonnes à sélectionner.
     * @return array|null Les données de réception ou null si non trouvée.
     */
    public function trouverReceptionParCles(string $numeroUtilisateur, string $idNotification, string $dateReception, array $colonnes = ['*']): ?array
    {
        return $this->trouverUnParCritere([
            'numero_utilisateur' => $numeroUtilisateur,
            'id_notification' => $idNotification,
            'date_reception' => $dateReception
        ], $colonnes);
    }

    /**
     * Met à jour une entrée de réception de notification spécifique par ses clés composées.
     * @param string $numeroUtilisateur Le numéro de l'utilisateur.
     * @param string $idNotification L'ID de la notification.
     * @param string $dateReception La date et heure de réception.
     * @param array $donnees Les données à mettre à jour.
     * @return bool Vrai si la mise à jour a réussi, faux sinon.
     */
    public function mettreAJourReceptionParCles(string $numeroUtilisateur, string $idNotification, string $dateReception, array $donnees): bool
    {
        return $this->mettreAJourParClesInternes([
            'numero_utilisateur' => $numeroUtilisateur,
            'id_notification' => $idNotification,
            'date_reception' => $dateReception
        ], $donnees);
    }

    /**
     * Supprime une entrée de réception de notification spécifique par ses clés composées.
     * @param string $numeroUtilisateur Le numéro de l'utilisateur.
     * @param string $idNotification L'ID de la notification.
     * @param string $dateReception La date et heure de réception.
     * @return bool Vrai si la suppression a réussi, faux sinon.
     */
    public function supprimerReceptionParCles(string $numeroUtilisateur, string $idNotification, string $dateReception): bool
    {
        return $this->supprimerParClesInternes([
            'numero_utilisateur' => $numeroUtilisateur,
            'id_notification' => $idNotification,
            'date_reception' => $dateReception
        ]);
    }
}