<?php

namespace App\Backend\Service\Notification;

interface ServiceNotificationInterface
{
    /**
     * Envoie une notification à un utilisateur spécifique.
     * @param string $numeroUtilisateur Le numéro de l'utilisateur destinataire.
     * @param string $idNotificationTemplate L'ID du modèle de notification à utiliser.
     * @param string $messageOverride Message personnalisé qui remplace le contenu du template si fourni.
     * @return bool Vrai si la notification a été envoyée.
     * @throws \Exception En cas d'erreur.
     */
    public function envoyerNotificationUtilisateur(string $numeroUtilisateur, string $idNotificationTemplate, string $messageOverride = ''): bool;

    /**
     * Envoie une notification à tous les utilisateurs d'un groupe spécifique.
     * @param string $idGroupeUtilisateur L'ID du groupe destinataire.
     * @param string $idNotificationTemplate L'ID du modèle de notification à utiliser.
     * @param string $messageOverride Message personnalisé qui remplace le contenu du template si fourni.
     * @return bool Vrai si au moins une notification a été envoyée.
     * @throws \Exception En cas d'erreur.
     */
    public function envoyerNotificationGroupe(string $idGroupeUtilisateur, string $idNotificationTemplate, string $messageOverride = ''): bool;

    /**
     * Récupère toutes les notifications d'un utilisateur.
     * @param string $numeroUtilisateur Le numéro de l'utilisateur.
     * @param bool $inclureLues Indique si les notifications lues doivent être incluses.
     * @return array Liste des notifications.
     */
    public function recupererNotificationsUtilisateur(string $numeroUtilisateur, bool $inclureLues = false): array;

    /**
     * Marque une notification spécifique comme lue pour un utilisateur.
     * @param string $idReception L'ID de la réception de la notification.
     * @return bool Vrai si la notification a été marquée comme lue.
     * @throws \Exception En cas d'erreur.
     */
    public function marquerNotificationCommeLue(string $idReception): bool;

    /**
     * Compte le nombre de notifications non lues pour un utilisateur.
     * @param string $numeroUtilisateur Le numéro de l'utilisateur.
     * @return int Le nombre de notifications non lues.
     */
    public function compterNotificationsNonLues(string $numeroUtilisateur): int;

    /**
     * Archive (supprime) les anciennes notifications lues pour un utilisateur.
     * @param string $numeroUtilisateur Le numéro de l'utilisateur.
     * @param int $joursAnciennete Le nombre de jours au-delà desquels archiver les notifications.
     * @return int Le nombre de notifications archivées.
     */
    public function archiverAnciennesNotificationsLues(string $numeroUtilisateur, int $joursAnciennete = 30): int;
}