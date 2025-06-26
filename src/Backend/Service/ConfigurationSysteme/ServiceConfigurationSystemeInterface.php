<?php

namespace App\Backend\Service\ConfigurationSysteme;

interface ServiceConfigurationSystemeInterface
{
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
     * Liste tous les types de documents de référence.
     * @return array
     */
    public function listerTypesDocument(): array;

    /**
     * Liste tous les niveaux d'étude.
     * @return array
     */
    public function listerNiveauxEtude(): array;

    /**
     * Liste tous les statuts de paiement.
     * @return array
     */
    public function listerStatutsPaiement(): array;

    /**
     * Liste toutes les décisions de passage.
     * @return array
     */
    public function listerDecisionsPassage(): array;

    /**
     * Liste tous les ECUEs.
     * @return array
     */
    public function listerEcues(): array;

    /**
     * Liste tous les grades.
     * @return array
     */
    public function listerGrades(): array;

    /**
     * Liste toutes les fonctions.
     * @return array
     */
    public function listerFonctions(): array;

    /**
     * Liste toutes les spécialités.
     * @return array
     */
    public function listerSpecialites(): array;

    /**
     * Liste tous les statuts de réclamation.
     * @return array
     */
    public function listerStatutsReclamation(): array;

    /**
     * Liste tous les statuts de conformité.
     * @return array
     */
    public function listerStatutsConformite(): array;

    /**
     * Liste toutes les Unités d'Enseignement (UE).
     * @return array
     */
    public function listerUes(): array;

    /**
     * Crée un élément dans un référentiel donné.
     * @param string $referentielCode Le code du référentiel.
     * @param array $donnees Les données de l'élément.
     * @return bool Vrai si la création a réussi.
     * @throws \Exception En cas d'erreur.
     */
    public function creerElementReferentiel(string $referentielCode, array $donnees): bool;

    /**
     * Modifie un élément dans un référentiel.
     * @param string $referentielCode Le code du référentiel.
     * @param string $id L'ID de l'élément.
     * @param array $donnees Les données à mettre à jour.
     * @return bool Vrai si la mise à jour a réussi.
     * @throws \Exception En cas d'erreur.
     */
    public function modifierElementReferentiel(string $referentielCode, string $id, array $donnees): bool;

    /**
     * Supprime un élément d'un référentiel.
     * @param string $referentielCode Le code du référentiel.
     * @param string $id L'ID de l'élément.
     * @return bool Vrai si la suppression a réussi.
     * @throws \Exception En cas d'erreur.
     */
    public function supprimerElementReferentiel(string $referentielCode, string $id): bool;

    /**
     * Récupère un élément d'un référentiel.
     * @param string $referentielCode Le code du référentiel.
     * @param string $id L'ID de l'élément.
     * @return array|null Les données de l'élément ou null.
     */
    public function recupererElementReferentiel(string $referentielCode, string $id): ?array;

    /**
     * Gère la relation entre un ECUE et une UE.
     * @param string $idEcue L'ID de l'ECUE.
     * @param string $idUe L'ID de l'UE.
     * @return bool Vrai si la liaison a réussi.
     * @throws \Exception En cas d'erreur.
     */
    public function lierEcueAUe(string $idEcue, string $idUe): bool;

    /**
     * Liste les ECUEs rattachés à une UE.
     * @param string $idUe L'ID de l'UE.
     * @return array Liste des ECUEs.
     */
    public function listerEcuesParUe(string $idUe): array;
}