<?php

declare(strict_types=1);

namespace App\Backend\Service\Interface;

use App\Backend\Exception\ModeleNonTrouveException;

interface NotificationServiceInterface
{
    /**
     * Envoie une notification interne à un utilisateur spécifique.
     *
     * @param string $idUtilisateur L'ID de l'utilisateur destinataire.
     * @param string $idTemplate Le code du modèle de notification.
     * @param array $variables Les variables à injecter dans le modèle.
     * @return bool True en cas de succès.
     * @throws ModeleNonTrouveException Si le modèle n'existe pas.
     */
    public function envoyerAUtilisateur(string $idUtilisateur, string $idTemplate, array $variables): bool;

    /**
     * Envoie une notification interne à tous les membres d'un groupe.
     *
     * @param string $idGroupe L'ID du groupe destinataire.
     * @param string $idTemplate Le code du modèle de notification.
     * @param array $variables Les variables à injecter dans le modèle.
     * @return bool True en cas de succès.
     */
    public function envoyerAGroupe(string $idGroupe, string $idTemplate, array $variables): bool;

    /**
     * Liste les notifications reçues par un utilisateur.
     *
     * @param string $idUtilisateur L'ID de l'utilisateur.
     * @return array La liste de ses notifications.
     */
    public function listerNotificationsPour(string $idUtilisateur): array;

    /**
     * Compte le nombre de notifications non lues pour un utilisateur.
     *
     * @param string $idUtilisateur L'ID de l'utilisateur.
     * @return int Le nombre de notifications non lues.
     */
    public function compterNotificationsNonLues(string $idUtilisateur): int;

    /**
     * Marque toutes les notifications d'un utilisateur comme lues.
     *
     * @param string $idUtilisateur L'ID de l'utilisateur.
     * @return bool True en cas de succès.
     */
    public function marquerToutesCommeLues(string $idUtilisateur): bool;

    /**
     * Supprime une notification de la vue d'un utilisateur.
     *
     * @param string $idReception L'ID de la réception de la notification.
     * @return bool True en cas de succès.
     */
    public function supprimerNotification(string $idReception): bool;
}