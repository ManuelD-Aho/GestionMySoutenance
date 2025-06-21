<?php
namespace App\Backend\Service\Permissions;

interface ServicePermissionsInterface
{
    /**
     * Crée un nouveau groupe d'utilisateurs.
     * @param string $idGroupeUtilisateur L'ID unique du groupe.
     * @param string $libelleGroupeUtilisateur Le libellé du groupe.
     * @return bool Vrai si le groupe a été créé.
     * @throws \Exception En cas d'erreur.
     */
    public function creerGroupeUtilisateur(string $idGroupeUtilisateur, string $libelleGroupeUtilisateur): bool;

    /**
     * Modifie un groupe d'utilisateurs existant.
     * @param string $idGroupeUtilisateur L'ID du groupe à modifier.
     * @param array $donnees Les données à mettre à jour.
     * @return bool Vrai si la mise à jour a réussi.
     * @throws \Exception En cas d'erreur.
     */
    public function modifierGroupeUtilisateur(string $idGroupeUtilisateur, array $donnees): bool;

    /**
     * Supprime un groupe d'utilisateurs.
     * @param string $idGroupeUtilisateur L'ID du groupe à supprimer.
     * @return bool Vrai si la suppression a réussi.
     * @throws \Exception En cas d'erreur.
     */
    public function supprimerGroupeUtilisateur(string $idGroupeUtilisateur): bool;

    /**
     * Récupère un groupe d'utilisateurs par son ID.
     * @param string $idGroupeUtilisateur L'ID du groupe.
     * @return array|null Les données du groupe ou null.
     */
    public function recupererGroupeUtilisateurParId(string $idGroupeUtilisateur): ?array;

    /**
     * Récupère un groupe d'utilisateurs par son code.
     * @param string $codeGroupe Le code du groupe.
     * @return array|null Les données du groupe ou null.
     */
    public function recupererGroupeUtilisateurParCode(string $codeGroupe): ?array;

    /**
     * Liste tous les groupes d'utilisateurs.
     * @return array
     */
    public function listerGroupesUtilisateur(): array;

    /**
     * Crée un nouveau type d'utilisateur.
     * @param string $idTypeUtilisateur L'ID unique du type.
     * @param string $libelleTypeUtilisateur Le libellé du type.
     * @return bool Vrai si le type a été créé.
     * @throws \Exception En cas d'erreur.
     */
    public function creerTypeUtilisateur(string $idTypeUtilisateur, string $libelleTypeUtilisateur): bool;

    /**
     * Modifie un type d'utilisateur existant.
     * @param string $idTypeUtilisateur L'ID du type à modifier.
     * @param array $donnees Les données à mettre à jour.
     * @return bool Vrai si la mise à jour a réussi.
     * @throws \Exception En cas d'erreur.
     */
    public function modifierTypeUtilisateur(string $idTypeUtilisateur, array $donnees): bool;

    /**
     * Supprime un type d'utilisateur.
     * @param string $idTypeUtilisateur L'ID du type à supprimer.
     * @return bool Vrai si la suppression a réussi.
     * @throws \Exception En cas d'erreur.
     */
    public function supprimerTypeUtilisateur(string $idTypeUtilisateur): bool;

    /**
     * Récupère un type d'utilisateur par son ID.
     * @param string $idTypeUtilisateur L'ID du type.
     * @return array|null Les données du type ou null.
     */
    public function recupererTypeUtilisateurParId(string $idTypeUtilisateur): ?array;

    /**
     * Récupère un type d'utilisateur par son code.
     * @param string $codeType Le code du type.
     * @return array|null Les données du type ou null.
     */
    public function recupererTypeUtilisateurParCode(string $codeType): ?array;

    /**
     * Liste tous les types d'utilisateurs.
     * @return array
     */
    public function listerTypesUtilisateur(): array;

    /**
     * Crée un nouveau niveau d'accès aux données.
     * @param string $idNiveauAcces L'ID unique du niveau d'accès.
     * @param string $libelleNiveauAcces Le libellé du niveau d'accès.
     * @return bool Vrai si le niveau d'accès a été créé.
     * @throws \Exception En cas d'erreur.
     */
    public function creerNiveauAcces(string $idNiveauAcces, string $libelleNiveauAcces): bool;

    /**
     * Modifie un niveau d'accès aux données existant.
     * @param string $idNiveauAcces L'ID du niveau d'accès à modifier.
     * @param array $donnees Les données à mettre à jour.
     * @return bool Vrai si la mise à jour a réussi.
     * @throws \Exception En cas d'erreur.
     */
    public function modifierNiveauAcces(string $idNiveauAcces, array $donnees): bool;

    /**
     * Supprime un niveau d'accès aux données.
     * @param string $idNiveauAcces L'ID du niveau d'accès à supprimer.
     * @return bool Vrai si la suppression a réussi.
     * @throws \Exception En cas d'erreur.
     */
    public function supprimerNiveauAcces(string $idNiveauAcces): bool;

    /**
     * Récupère un niveau d'accès aux données par son ID.
     * @param string $idNiveauAcces L'ID du niveau d'accès.
     * @return array|null Les données du niveau d'accès ou null.
     */
    public function recupererNiveauAccesParId(string $idNiveauAcces): ?array;

    /**
     * Récupère un niveau d'accès aux données par son code.
     * @param string $codeNiveau Le code du niveau d'accès.
     * @return array|null Les données du niveau d'accès ou null.
     */
    public function recupererNiveauAccesParCode(string $codeNiveau): ?array;

    /**
     * Liste tous les niveaux d'accès aux données.
     * @return array
     */
    public function listerNiveauxAcces(): array;

    /**
     * Crée un nouveau traitement (permission).
     * @param string $idTraitement L'ID unique du traitement.
     * @param string $libelleTraitement Le libellé du traitement.
     * @return bool Vrai si le traitement a été créé.
     * @throws \Exception En cas d'erreur.
     */
    public function creerTraitement(string $idTraitement, string $libelleTraitement): bool;

    /**
     * Modifie un traitement (permission) existant.
     * @param string $idTraitement L'ID du traitement à modifier.
     * @param array $donnees Les données à mettre à jour.
     * @return bool Vrai si la mise à jour a réussi.
     * @throws \Exception En cas d'erreur.
     */
    public function modifierTraitement(string $idTraitement, array $donnees): bool;

    /**
     * Supprime un traitement (permission).
     * @param string $idTraitement L'ID du traitement à supprimer.
     * @return bool Vrai si la suppression a réussi.
     * @throws \Exception En cas d'erreur.
     */
    public function supprimerTraitement(string $idTraitement): bool;

    /**
     * Récupère un traitement (permission) par son ID.
     * @param string $idTraitement L'ID du traitement.
     * @return array|null Les données du traitement ou null.
     */
    public function recupererTraitementParId(string $idTraitement): ?array;

    /**
     * Récupère un traitement (permission) par son code.
     * @param string $codeTraitement Le code du traitement.
     * @return array|null Les données du traitement ou null.
     */
    public function recupererTraitementParCode(string $codeTraitement): ?array;

    /**
     * Liste tous les traitements (permissions).
     * @return array
     */
    public function listerTraitements(): array;

    /**
     * Attribue une permission (traitement) à un groupe d'utilisateurs.
     * @param string $idGroupeUtilisateur L'ID du groupe.
     * @param string $idTraitement L'ID du traitement (permission).
     * @return bool Vrai si l'attribution a réussi.
     * @throws \Exception En cas d'erreur.
     */
    public function attribuerPermissionGroupe(string $idGroupeUtilisateur, string $idTraitement): bool;

    /**
     * Retire une permission (traitement) d'un groupe d'utilisateurs.
     * @param string $idGroupeUtilisateur L'ID du groupe.
     * @param string $idTraitement L'ID du traitement (permission).
     * @return bool Vrai si le retrait a réussi.
     * @throws \Exception En cas d'erreur.
     */
    public function retirerPermissionGroupe(string $idGroupeUtilisateur, string $idTraitement): bool;

    /**
     * Récupère la liste des permissions (traitements) pour un groupe donné.
     * @param string $idGroupeUtilisateur L'ID du groupe utilisateur.
     * @return array La liste des IDs de traitements.
     * @throws \App\Backend\Exception\ElementNonTrouveException Si le groupe n'est pas trouvé.
     */
    public function recupererPermissionsPourGroupe(string $idGroupeUtilisateur): array;

    /**
     * Récupère la liste des groupes auxquels une permission est attribuée.
     * @param string $idTraitement L'ID du traitement (permission).
     * @return array La liste des IDs de groupes utilisateurs.
     * @throws \App\Backend\Exception\ElementNonTrouveException Si le traitement n'est pas trouvé.
     */
    public function recupererGroupesPourPermission(string $idTraitement): array;

    /**
     * Vérifie si l'utilisateur connecté (dans la session) possède une permission spécifique.
     * @param string $permissionCode Le code de la permission à vérifier.
     * @return bool Vrai si l'utilisateur possède la permission, faux sinon.
     */
    public function utilisateurPossedePermission(string $permissionCode): bool;

    /**
     * Vérifie si un groupe possède une permission spécifique.
     * @param string $idGroupeUtilisateur L'ID du groupe.
     * @param string $permissionCode Le code de la permission à vérifier.
     * @return bool Vrai si le groupe possède la permission, faux sinon.
     * @throws \App\Backend\Exception\ElementNonTrouveException Si le groupe n'est pas trouvé.
     */
    public function groupePossedePermission(string $idGroupeUtilisateur, string $permissionCode): bool;
}