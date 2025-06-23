<?php
namespace App\Backend\Service\Messagerie;

interface ServiceMessagerieInterface
{
    public function demarrerOuRecupererConversationDirecte(string $numeroUtilisateur1, string $numeroUtilisateur2): string;
    public function creerNouvelleConversationDeGroupe(string $nomConversation, string $numeroCreateur, array $numerosParticipants): string;
    public function envoyerMessageDansConversation(string $idConversation, string $numeroExpediteur, string $contenuMessage): string;
    public function recupererMessagesDuneConversation(string $idConversation, int $limit = 50, int $offset = 0): array;
    public function listerConversationsPourUtilisateur(string $numeroUtilisateur): array;
    public function marquerMessagesCommeLus(string $numeroUtilisateur, string|array $idMessageChat): bool;
    public function ajouterParticipant(string $idConversation, array $numerosUtilisateurs): bool;
    public function retirerParticipant(string $idConversation, array $numerosUtilisateurs): bool;
}