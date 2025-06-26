<?php

namespace App\Backend\Service\NotificationConfiguration;

interface ServiceNotificationConfigurationInterface
{
    /**
     * Configure une règle de diffusion dans la matrice des notifications.
     * @param string $idActionDeclencheur L'ID de l'action qui déclenche la notification.
     * @param string $idGroupeDestinataire L'ID du groupe destinataire.
     * @param string $canalNotification Le canal de communication ('Interne', 'Email', 'Tous').
     * @param bool $estActive Indique si la règle est active.
     * @return bool Vrai si la configuration a réussi.
     * @throws \Exception En cas d'erreur.
     */
    public function configurerMatriceDiffusion(string $idActionDeclencheur, string $idGroupeDestinataire, string $canalNotification, bool $estActive): bool;

    /**
     * Récupère la configuration complète de la matrice de diffusion.
     * @return array La matrice de diffusion.
     */
    public function recupererMatriceDiffusion(): array;

    /**
     * Définit les préférences de notification pour un utilisateur.
     * @param string $numeroUtilisateur Le numéro de l'utilisateur.
     * @param array $preferences Tableau associatif des préférences (ex: ['email_summary' => true]).
     * @return bool Vrai si la mise à jour a réussi.
     * @throws \Exception En cas d'erreur.
     */
    public function definirPreferencesNotificationUtilisateur(string $numeroUtilisateur, array $preferences): bool;

    /**
     * Récupère les préférences de notification d'un utilisateur.
     * @param string $numeroUtilisateur Le numéro de l'utilisateur.
     * @return array Les préférences de l'utilisateur.
     */
    public function recupererPreferencesNotificationUtilisateur(string $numeroUtilisateur): ?array;

    /**
     * Liste les types de notifications considérées comme critiques et non désactivables.
     * @return array La liste des codes d'action critiques.
     */
    public function listerNotificationsCritiques(): array;
}