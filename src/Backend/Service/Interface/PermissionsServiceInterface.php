<?php

declare(strict_types=1);

namespace App\Backend\Service\Interface;

interface PermissionsServiceInterface
{
    /**
     * Calcule et retourne la liste complète et dédupliquée des permissions (traitements)
     * pour un utilisateur donné, en incluant les permissions de son groupe de base
     * et toutes les permissions actives qui lui sont déléguées.
     *
     * @param string $numeroUtilisateur L'identifiant de l'utilisateur.
     * @return array La liste des identifiants de traitement auxquels l'utilisateur a accès.
     */
    public function getPermissionsPourSession(string $numeroUtilisateur): array;

    /**
     * Vérifie si un utilisateur possède une permission spécifique.
     * Cette méthode doit utiliser le cache de session si disponible, ou recalculer les droits.
     *
     * @param string $numeroUtilisateur L'identifiant de l'utilisateur.
     * @param string $idTraitement L'identifiant du traitement à vérifier.
     * @return bool True si l'utilisateur possède la permission, false sinon.
     */
    public function utilisateurPossedePermission(string $numeroUtilisateur, string $idTraitement): bool;

    /**
     * Force la synchronisation des permissions pour toutes les sessions actives d'un utilisateur.
     * Utile après un changement de groupe, de rôle ou de délégation pour une application immédiate
     * sans que l'utilisateur ait besoin de se déconnecter/reconnecter.
     *
     * @param string $numeroUtilisateur L'identifiant de l'utilisateur dont les sessions doivent être mises à jour.
     */
    public function synchroniserPermissionsPourSessionsActives(string $numeroUtilisateur): void;
}