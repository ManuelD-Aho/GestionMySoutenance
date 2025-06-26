<?php

namespace App\Backend\Service\Messagerie;

interface ServiceMessagerieInterface
{
    /**
     * Démarre une conversation directe entre deux utilisateurs, ou récupère l'existante.
     * @param string $numeroUtilisateur1 Le numéro du premier utilisateur.
     * @param string $numeroUtilisateur2 Le numéro du second utilisateur.
     * @return string L'ID de la conversation.
     * @throws \Exception En cas d'erreur.
     */
    public function demarrerOuRecupererConversationDirecte(string $numeroUtilisateur1, string $numeroUtilisateur2): string;

    /**
     * Crée une nouvelle conversation de groupe.
     * @param string $nomConversation Le nom du groupe.
     * @param string $numeroCreateur Le numéro de l'utilisateur créateur du groupe.
     * @param array $numerosParticipants Tableau des numéros d'utilisateurs participants.
     * @return string L'ID de la conversation de groupe créée.
     * @throws \Exception En cas d'erreur.
     */
    public function creerNouvelleConversationDeGroupe(string $nomConversation, string $numeroCreateur, array $numerosParticipants): string;

    /**
     * Envoie un message dans une conversation donnée.
     * @param string $idConversation L'ID de la conversation.
     * @param string $numeroExpediteur Le numéro de l'utilisateur expéditeur.
     * @param string $contenuMessage Le contenu du message.
     * @return string L'ID du message de chat créé.
     * @throws \Exception En cas d'erreur.
     */
    public function envoyerMessageDansConversation(string $idConversation, string $numeroExpediteur, string $contenuMessage): string;

    /**
     * Récupère les messages d'une conversation donnée.
     * @param string $idConversation L'ID de la conversation.
     * @param int $limit Le nombre maximum de messages à récupérer.
     * @param int $offset L'offset pour la pagination.
     * @return array Liste des messages de chat.
     * @throws \App\Backend\Exception\ElementNonTrouveException Si la conversation n'existe pas.
     */
    public function recupererMessagesDuneConversation(string $idConversation, int $limit = 50, int $offset = 0): array;

    /**
     * Liste toutes les conversations auxquelles un utilisateur participe.
     * @param string $numeroUtilisateur Le numéro de l'utilisateur.
     * @return array Liste des conversations.
     */
    public function listerConversationsPourUtilisateur(string $numeroUtilisateur): array;

    /**
     * Marque un ou plusieurs messages comme lus pour un utilisateur donné.
     * @param string $numeroUtilisateur Le numéro de l'utilisateur.
     * @param string|array $idMessageChat L'ID du message ou un tableau d'IDs de messages à marquer comme lus.
     * @return bool Vrai si la mise à jour a réussi.
     */
    public function marquerMessagesCommeLus(string $numeroUtilisateur, string|array $idMessageChat): bool;

    /**
     * Ajoute un ou plusieurs participants à une conversation de groupe.
     * @param string $idConversation L'ID de la conversation de groupe.
     * @param array $numerosUtilisateurs Tableau des numéros d'utilisateurs à ajouter.
     * @return bool Vrai si tous les participants ont été ajoutés.
     * @throws \Exception En cas d'erreur.
     */
    public function ajouterParticipant(string $idConversation, array $numerosUtilisateurs): bool;

    /**
     * Retire un ou plusieurs participants d'une conversation de groupe.
     * @param string $idConversation L'ID de la conversation de groupe.
     * @param array $numerosUtilisateurs Tableau des numéros d'utilisateurs à retirer.
     * @return bool Vrai si tous les participants ont été retirés.
     * @throws \Exception En cas d'erreur.
     */
    public function retirerParticipant(string $idConversation, array $numerosUtilisateurs): bool;

    /**
     * Récupère les détails d'une conversation spécifique par son ID.
     * @param string $idConversation L'ID de la conversation.
     * @return array|null Les détails de la conversation ou null si non trouvée.
     */
    public function getConversationDetails(string $idConversation): ?array;

    /**
     * Vérifie si un utilisateur est participant d'une conversation donnée.
     * @param string $idConversation L'ID de la conversation.
     * @param string $numeroUtilisateur Le numéro de l'utilisateur.
     * @return bool Vrai si l'utilisateur est participant, faux sinon.
     */
    public function estParticipant(string $idConversation, string $numeroUtilisateur): bool;
}