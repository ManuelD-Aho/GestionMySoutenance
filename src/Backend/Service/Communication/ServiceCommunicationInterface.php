<?php

namespace App\Backend\Service\Communication;

interface ServiceCommunicationInterface
{
    /**
     * Envoie un message privé entre utilisateurs.
     * @param string $expediteur Le numéro de l'expéditeur.
     * @param string $destinataire Le numéro du destinataire.
     * @param string $sujet Le sujet du message.
     * @param string $contenu Le contenu du message.
     * @param array $options Les options (priorité, accusé de réception, etc.).
     * @return string L'ID du message créé.
     */
    public function envoyerMessagePrive(string $expediteur, string $destinataire, string $sujet, string $contenu, array $options = []): string;

    /**
     * Crée une conversation de groupe.
     * @param string $createur Le numéro du créateur.
     * @param array $participants Les numéros des participants.
     * @param string $titre Le titre de la conversation.
     * @param array $parametres Les paramètres de la conversation.
     * @return string L'ID de la conversation créée.
     */
    public function creerConversationGroupe(string $createur, array $participants, string $titre, array $parametres = []): string;

    /**
     * Ajoute un participant à une conversation.
     * @param string $idConversation L'ID de la conversation.
     * @param string $numeroUtilisateur Le numéro de l'utilisateur à ajouter.
     * @param string $role Le rôle du participant.
     * @return bool Vrai si l'ajout a réussi.
     */
    public function ajouterParticipant(string $idConversation, string $numeroUtilisateur, string $role = 'MEMBRE'): bool;

    /**
     * Retire un participant d'une conversation.
     * @param string $idConversation L'ID de la conversation.
     * @param string $numeroUtilisateur Le numéro de l'utilisateur à retirer.
     * @return bool Vrai si le retrait a réussi.
     */
    public function retirerParticipant(string $idConversation, string $numeroUtilisateur): bool;

    /**
     * Envoie un message dans une conversation.
     * @param string $idConversation L'ID de la conversation.
     * @param string $expediteur Le numéro de l'expéditeur.
     * @param string $contenu Le contenu du message.
     * @param array $pieceJointes Les pièces jointes.
     * @return string L'ID du message créé.
     */
    public function envoyerMessageConversation(string $idConversation, string $expediteur, string $contenu, array $pieceJointes = []): string;

    /**
     * Marque un message comme lu.
     * @param string $idMessage L'ID du message.
     * @param string $numeroUtilisateur Le numéro de l'utilisateur.
     * @return bool Vrai si le marquage a réussi.
     */
    public function marquerCommeLu(string $idMessage, string $numeroUtilisateur): bool;

    /**
     * Archive une conversation.
     * @param string $idConversation L'ID de la conversation.
     * @param string $numeroUtilisateur Le numéro de l'utilisateur.
     * @return bool Vrai si l'archivage a réussi.
     */
    public function archiverConversation(string $idConversation, string $numeroUtilisateur): bool;

    /**
     * Récupère les conversations d'un utilisateur.
     * @param string $numeroUtilisateur Le numéro de l'utilisateur.
     * @param array $filtres Les filtres (archivées, non lues, etc.).
     * @return array La liste des conversations.
     */
    public function obtenirConversationsUtilisateur(string $numeroUtilisateur, array $filtres = []): array;

    /**
     * Récupère les messages d'une conversation.
     * @param string $idConversation L'ID de la conversation.
     * @param string $numeroUtilisateur Le numéro de l'utilisateur.
     * @param int $limite Le nombre de messages à récupérer.
     * @param int $offset Le décalage pour la pagination.
     * @return array La liste des messages.
     */
    public function obtenirMessagesConversation(string $idConversation, string $numeroUtilisateur, int $limite = 50, int $offset = 0): array;

    /**
     * Recherche dans les messages.
     * @param string $numeroUtilisateur Le numéro de l'utilisateur.
     * @param string $terme Le terme de recherche.
     * @param array $filtres Les filtres de recherche.
     * @return array Les résultats de la recherche.
     */
    public function rechercherMessages(string $numeroUtilisateur, string $terme, array $filtres = []): array;

    /**
     * Configure les notifications de messagerie.
     * @param string $numeroUtilisateur Le numéro de l'utilisateur.
     * @param array $preferences Les préférences de notification.
     * @return bool Vrai si la configuration a réussi.
     */
    public function configurerNotifications(string $numeroUtilisateur, array $preferences): bool;

    /**
     * Exporte une conversation.
     * @param string $idConversation L'ID de la conversation.
     * @param string $format Le format d'export (PDF, HTML, TXT).
     * @return string Le chemin du fichier exporté.
     */
    public function exporterConversation(string $idConversation, string $format = 'PDF'): string;
}