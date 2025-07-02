<?php
// src/Backend/Service/Communication/ServiceCommunicationInterface.php

namespace App\Backend\Service\Communication;

interface ServiceCommunicationInterface
{
    // --- Section 1: Envoi de Messages ---
    public function envoyerNotificationInterne(string $numeroUtilisateur, string $idNotificationTemplate, array $variables = []): bool;
    public function envoyerNotificationGroupe(string $idGroupeUtilisateur, string $idNotificationTemplate, array $variables = []): bool;
    public function envoyerEmail(string $destinataireEmail, string $idNotificationTemplate, array $variables = [], array $piecesJointes = []): bool;

    // --- Section 2: Messagerie Instantanée ---
    public function demarrerConversation(array $participantsIds, ?string $nomConversation = null): string;
    public function envoyerMessageChat(string $idConversation, string $expediteurId, string $contenu, ?array $pieceJointe = null): string;
    public function listerConversationsPourUtilisateur(string $numeroUtilisateur): array;
    public function listerMessagesPourConversation(string $idConversation): array;

    // --- Section 3: Consultation & Gestion des Notifications ---
    public function listerNotificationsNonLues(string $numeroUtilisateur): array;
    public function marquerNotificationLue(string $idReception): bool;
    public function listerModelesNotification(): array;
    public function mettreAJourModeleNotification(string $id, string $libelle, string $contenu): bool;
    public function listerReglesMatrice(): array;
    public function mettreAJourRegleMatrice(string $idRegle, string $canal, bool $estActive): bool;
    public function archiverConversationsInactives(int $jours): int;
}