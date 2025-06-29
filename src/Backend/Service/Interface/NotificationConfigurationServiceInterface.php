<?php

declare(strict_types=1);

namespace App\Backend\Service\Interface;

use App\Backend\Exception\ElementNonTrouveException;

interface NotificationConfigurationServiceInterface
{
    /**
     * Crée une nouvelle règle dans la matrice de diffusion des notifications.
     *
     * @param array $donnees Données de la règle (événement, groupe, canal).
     * @return string L'ID de la nouvelle règle.
     */
    public function creerRegle(array $donnees): string;

    /**
     * Met à jour une règle de diffusion existante.
     *
     * @param string $idRegle L'ID de la règle.
     * @param array $donnees Les données à modifier.
     * @return bool True en cas de succès.
     * @throws ElementNonTrouveException Si la règle n'existe pas.
     */
    public function mettreAJourRegle(string $idRegle, array $donnees): bool;

    /**
     * Liste toutes les règles de la matrice de diffusion.
     *
     * @return array La liste des règles.
     */
    public function listerRegles(): array;

    /**
     * Met à jour les préférences de notification d'un utilisateur.
     *
     * @param string $idUtilisateur L'ID de l'utilisateur.
     * @param array $preferences Les préférences (ex: résumé quotidien).
     * @return bool True en cas de succès.
     */
    public function mettreAJourPreferencesUtilisateur(string $idUtilisateur, array $preferences): bool;
}