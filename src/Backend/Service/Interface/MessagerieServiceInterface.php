<?php

declare(strict_types=1);

namespace App\Backend\Service\Interface;

use App\Backend\Exception\DoublonException;
use App\Backend\Exception\ElementNonTrouveException;
use App\Backend\Exception\PermissionException;

interface MessagerieServiceInterface
{
    /**
     * Crée une nouvelle conversation, directe ou de groupe.
     *
     * @param array $participants IDs des utilisateurs participants.
     * @param string|null $nomGroupe Le nom si c'est une conversation de groupe.
     * @return string L'ID de la nouvelle conversation.
     * @throws DoublonException Si une conversation directe entre les mêmes participants existe déjà.
     */
    public function creerConversation(array $participants, ?string $nomGroupe): string;

    /**
     * Envoie un message dans une conversation.
     *
     * @param string $idConversation L'ID de la conversation.
     * @param string $idExpediteur L'ID de l'expéditeur.
     * @param string $contenu Le contenu du message.
     * @return string L'ID du nouveau message.
     * @throws PermissionException Si l'expéditeur ne fait pas partie de la conversation.
     */
    public function envoyerMessage(string $idConversation, string $idExpediteur, string $contenu): string;

    /**
     * Liste les conversations d'un utilisateur.
     *
     * @param string $idUtilisateur L'ID de l'utilisateur.
     * @return array La liste de ses conversations.
     */
    public function listerConversationsPour(string $idUtilisateur): array;

    /**
     * Récupère les messages d'une conversation avec pagination.
     *
     * @param string $idConversation L'ID de la conversation.
     * @param array $options Options de pagination (limit, offset).
     * @return array La liste des messages.
     */
    public function getMessagesDeConversation(string $idConversation, array $options = []): array;

    /**
     * Marque un message comme lu par un utilisateur.
     *
     * @param string $idMessage L'ID du message.
     * @param string $idUtilisateur L'ID de l'utilisateur.
     * @return bool True en cas de succès.
     */
    public function marquerCommeLu(string $idMessage, string $idUtilisateur): bool;

    /**
     * Ajoute un participant à une conversation de groupe.
     *
     * @param string $idConversation L'ID de la conversation.
     * @param string $idUtilisateur L'ID de l'utilisateur à ajouter.
     * @return bool True en cas de succès.
     */
    public function ajouterParticipantAGroupe(string $idConversation, string $idUtilisateur): bool;

    /**
     * Retire un participant d'une conversation de groupe.
     *
     * @param string $idConversation L'ID de la conversation.
     * @param string $idUtilisateur L'ID de l'utilisateur à retirer.
     * @return bool True en cas de succès.
     */
    public function retirerParticipantDeGroupe(string $idConversation, string $idUtilisateur): bool;

    /**
     * Archive une conversation pour un utilisateur (la masque de la vue principale).
     *
     * @param string $idConversation L'ID de la conversation.
     * @param string $idUtilisateur L'ID de l'utilisateur.
     * @return bool True en cas de succès.
     */
    public function archiverConversation(string $idConversation, string $idUtilisateur): bool;
}