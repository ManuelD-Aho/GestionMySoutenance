<?php
// src/Backend/Service/Communication/ServiceCommunicationInterface.php

namespace App\Backend\Service\Communication;

interface ServiceCommunicationInterface
{
    // --- Section 1: Envoi de Messages ---

    /**
     * Envoie une notification interne à un utilisateur spécifique.
     *
     * @param string $numeroUtilisateur L'ID de l'utilisateur destinataire.
     * @param string $idNotificationTemplate L'ID du modèle de notification.
     * @param array $variables Pour remplacer les placeholders dans le message.
     * @return bool True si la notification a été créée.
     */
    public function envoyerNotificationInterne(string $numeroUtilisateur, string $idNotificationTemplate, array $variables = []): bool;

    /**
     * Envoie une notification interne à tous les membres d'un groupe.
     *
     * @param string $idGroupeUtilisateur L'ID du groupe destinataire.
     * @param string $idNotificationTemplate L'ID du modèle de notification.
     * @param array $variables Pour remplacer les placeholders dans le message.
     * @return bool True si au moins une notification a été envoyée.
     */
    public function envoyerNotificationGroupe(string $idGroupeUtilisateur, string $idNotificationTemplate, array $variables = []): bool;

    /**
     * Envoie un email en utilisant un modèle et des variables.
     *
     * @param string $destinataireEmail L'adresse email du destinataire.
     * @param string $idNotificationTemplate L'ID du modèle de notification/email.
     * @param array $variables Pour remplacer les placeholders dans le sujet et le corps.
     * @return bool True si l'email a été envoyé avec succès.
     */
    public function envoyerEmail(string $destinataireEmail, string $idNotificationTemplate, array $variables = []): bool;

    // --- Section 2: Messagerie Instantanée ---

    /**
     * Démarre une conversation directe entre deux utilisateurs ou récupère l'existante.
     *
     * @param string $initiateurId L'ID de l'utilisateur qui initie.
     * @param string $destinataireId L'ID de l'autre participant.
     * @return string L'ID de la conversation.
     */
    public function demarrerConversationDirecte(string $initiateurId, string $destinataireId): string;

    /**
     * Envoie un message dans une conversation.
     *
     * @param string $idConversation L'ID de la conversation.
     * @param string $expediteurId L'ID de l'expéditeur.
     * @param string $contenu Le contenu du message.
     * @return string L'ID du message envoyé.
     */
    public function envoyerMessageChat(string $idConversation, string $expediteurId, string $contenu): string;

    // --- Section 3: Consultation ---

    /**
     * Récupère les notifications non lues pour un utilisateur.
     *
     * @param string $numeroUtilisateur L'ID de l'utilisateur.
     * @return array La liste des notifications.
     */
    public function listerNotificationsNonLues(string $numeroUtilisateur): array;

    /**
     * Marque une notification comme lue.
     *
     * @param string $idReception L'ID de la réception de la notification.
     * @return bool True en cas de succès.
     */
    public function marquerNotificationLue(string $idReception): bool;
}