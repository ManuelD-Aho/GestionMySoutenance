<?php

namespace App\Backend\Service\Utilisateur;

interface ServiceUtilisateurInterface
{
    /**
     * Crée un nouvel utilisateur complet.
     * @param array $donneesUtilisateur Les données de base de l'utilisateur.
     * @param array $donneesProfil Les données spécifiques au profil.
     * @param string $typeProfilCode Le code du type de profil.
     * @return string Le numéro de l'utilisateur créé.
     */
    public function creerUtilisateurComplet(array $donneesUtilisateur, array $donneesProfil, string $typeProfilCode): string;

    /**
     * Met à jour les informations d'un utilisateur.
     * @param string $numeroUtilisateur Le numéro de l'utilisateur.
     * @param array $donneesUtilisateur Les nouvelles données.
     * @return bool Vrai si la mise à jour a réussi.
     */
    public function mettreAJourUtilisateur(string $numeroUtilisateur, array $donneesUtilisateur): bool;

    /**
     * Récupère les informations complètes d'un utilisateur.
     * @param string $numeroUtilisateur Le numéro de l'utilisateur.
     * @return array|null Les données de l'utilisateur ou null.
     */
    public function obtenirUtilisateurComplet(string $numeroUtilisateur): ?array;

    /**
     * Liste les utilisateurs selon des critères.
     * @param array $criteres Les critères de recherche.
     * @param int $page Le numéro de page.
     * @param int $elementsParPage Le nombre d'éléments par page.
     * @return array La liste paginée des utilisateurs.
     */
    public function listerUtilisateurs(array $criteres = [], int $page = 1, int $elementsParPage = 20): array;

    /**
     * Active ou désactive un compte utilisateur.
     * @param string $numeroUtilisateur Le numéro de l'utilisateur.
     * @param string $statut Le nouveau statut (ACTIF, INACTIF, BLOQUE).
     * @param string|null $raison La raison du changement.
     * @return bool Vrai si le changement a réussi.
     */
    public function changerStatutUtilisateur(string $numeroUtilisateur, string $statut, ?string $raison = null): bool;

    /**
     * Supprime un utilisateur (suppression logique).
     * @param string $numeroUtilisateur Le numéro de l'utilisateur.
     * @param string $raison La raison de la suppression.
     * @return bool Vrai si la suppression a réussi.
     */
    public function supprimerUtilisateur(string $numeroUtilisateur, string $raison): bool;

    /**
     * Recherche des utilisateurs par critères multiples.
     * @param string $terme Le terme de recherche.
     * @param array $filtres Les filtres à appliquer.
     * @return array Les résultats de la recherche.
     */
    public function rechercherUtilisateurs(string $terme, array $filtres = []): array;

    /**
     * Importe des utilisateurs en masse depuis un fichier.
     * @param string $cheminFichier Le chemin vers le fichier.
     * @param string $format Le format du fichier (CSV, XLSX).
     * @return array Le résultat de l'import.
     */
    public function importerUtilisateursEnMasse(string $cheminFichier, string $format): array;

    /**
     * Exporte la liste des utilisateurs.
     * @param array $criteres Les critères de sélection.
     * @param string $format Le format d'export (CSV, PDF, XLSX).
     * @return string Le chemin vers le fichier généré.
     */
    public function exporterUtilisateurs(array $criteres, string $format): string;

    /**
     * Récupère l'historique des actions d'un utilisateur.
     * @param string $numeroUtilisateur Le numéro de l'utilisateur.
     * @param int $limite Le nombre d'actions à récupérer.
     * @return array L'historique des actions.
     */
    public function obtenirHistoriqueUtilisateur(string $numeroUtilisateur, int $limite = 50): array;

    /**
     * Gère les délégations d'un utilisateur.
     * @param string $numeroUtilisateur Le numéro de l'utilisateur.
     * @param string $action L'action (CREER, MODIFIER, SUPPRIMER).
     * @param array $donneesDelegation Les données de délégation.
     * @return bool Vrai si l'opération a réussi.
     */
    public function gererDelegation(string $numeroUtilisateur, string $action, array $donneesDelegation): bool;

    /**
     * Vérifie si un utilisateur a une délégation active.
     * @param string $numeroUtilisateur Le numéro de l'utilisateur.
     * @param string $typeDelegation Le type de délégation.
     * @return bool Vrai si l'utilisateur a une délégation active.
     */
    public function aDelegationActive(string $numeroUtilisateur, string $typeDelegation): bool;
}