<?php
namespace App\Backend\Service\ConfigurationSysteme;

interface ServiceConfigurationSystemeInterface
{
    /**
     * Définit l'année académique active.
     * @param string $idAnneeAcademique L'ID de l'année académique à activer.
     * @return bool Vrai si l'année a été activée.
     * @throws \Exception En cas d'erreur.
     */
    public function definirAnneeAcademiqueActive(string $idAnneeAcademique): bool;

    /**
     * Met à jour les paramètres généraux du système.
     * @param array $parametres Tableau associatif des paramètres à mettre à jour.
     * @return bool Vrai si la mise à jour réussit.
     * @throws \Exception En cas d'erreur.
     */
    public function mettreAJourParametresGeneraux(array $parametres): bool;

    /**
     * Récupère les paramètres généraux du système.
     * @return array Tableau associatif des paramètres.
     */
    public function recupererParametresGeneraux(): array;

    /**
     * Gère la création ou la mise à jour des modèles de notification/email.
     * @param string|null $idNotification L'ID de la notification si mise à jour.
     * @param array $donnees Les données du modèle de notification.
     * @return string L'ID de la notification créée ou mise à jour.
     * @throws \Exception En cas d'erreur.
     */
    public function gererModeleNotificationEmail(?string $idNotification, array $donnees): string;

    /**
     * Liste toutes les années académiques.
     * @return array
     */
    public function listerAnneesAcademiques(): array;

    /**
     * Liste tous les types de documents de référence.
     * @return array
     */
    public function listerTypesDocument(): array;
}