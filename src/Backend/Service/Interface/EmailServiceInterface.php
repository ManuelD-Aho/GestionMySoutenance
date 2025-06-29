<?php

declare(strict_types=1);

namespace App\Backend\Service\Interface;

use App\Backend\Exception\EmailException;
use App\Backend\Exception\ModeleNonTrouveException;

interface EmailServiceInterface
{
    /**
     * Envoie un email simple.
     *
     * @param string $destinataire L'adresse email du destinataire.
     * @param string $sujet Le sujet de l'email.
     * @param string $corpsHtml Le corps de l'email au format HTML.
     * @param array $options Options supplémentaires (ex: pièce jointe, CC, BCC).
     * @return bool True si l'envoi a réussi.
     * @throws EmailException En cas d'échec de l'envoi.
     */
    public function envoyer(string $destinataire, string $sujet, string $corpsHtml, array $options = []): bool;

    /**
     * Envoie un email basé sur un modèle et des variables.
     *
     * @param string $destinataire L'adresse email du destinataire.
     * @param string $templateCode Le code identifiant le modèle d'email.
     * @param array $variables Les variables à substituer dans le modèle.
     * @return bool True si l'envoi a réussi.
     * @throws ModeleNonTrouveException Si le modèle n'existe pas.
     */
    public function envoyerDepuisTemplate(string $destinataire, string $templateCode, array $variables): bool;

    /**
     * Ajoute un email à la file d'attente pour un envoi asynchrone par un worker.
     *
     * @param array $parametres Les paramètres de l'email (destinataire, sujet, corps, etc.).
     * @return bool True si l'email a été ajouté à la file.
     */
    public function ajouterEmailALaFile(array $parametres): bool;

    /**
     * Gère un envoi massif d'emails, potentiellement en utilisant la file d'attente.
     *
     * @param array $destinataires La liste des adresses email.
     * @param string $sujet Le sujet de l'email.
     * @param string $corps Le corps de l'email.
     * @return int Le nombre d'emails envoyés ou mis en file.
     */
    public function envoyerEnMasse(array $destinataires, string $sujet, string $corps): int;

    /**
     * Récupère le statut d'un email envoyé via la file d'attente.
     *
     * @param string $idEmail L'ID de l'email dans la file.
     * @return string|null Le statut ('en attente', 'envoyé', 'échoué') ou null.
     */
    public function getStatutEnvoi(string $idEmail): ?string;
}