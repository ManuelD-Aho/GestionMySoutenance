<?php

namespace App\Backend\Service\Permissions;

use App\Backend\Exception\PermissionException;
use App\Backend\Exception\ElementNonTrouveException;
use App\Backend\Exception\OperationImpossibleException;
use App\Backend\Exception\DoublonException;
use App\Backend\Exception\ValidationException;
use PDOException;
/**
 * Interface pour la gestion des permissions et des groupes d'utilisateurs.
 * Permet de créer, modifier, supprimer et lister les groupes, types d'utilisateurs, niveaux d'accès et traitements.
 * Gère également l'attribution et le retrait de permissions aux groupes.
 */
interface ServicePermissionsInterface
{
    /**
     * Crée un nouveau groupe d'utilisateurs.
     *
     * @param string $libelle Le libellé du groupe.
     * @param string|null $description La description optionnelle du groupe.
     * @param string|null $codeGroupe Le code unique optionnel pour le groupe.
     * @return int L'ID du groupe créé.
     * @throws DoublonException Si le libellé ou le code du groupe existe déjà.
     * @throws ValidationException Si les données sont invalides.
     * @throws PDOException En cas d'erreur de base de données.
     */
    public function creerGroupeUtilisateur(string $libelle, ?string $description, ?string $codeGroupe = null): int;

    /**
     * Modifie un groupe d'utilisateurs existant.
     *
     * @param int $idGroupe L'ID du groupe à modifier.
     * @param string $libelle Le nouveau libellé du groupe.
     * @param string|null $description La nouvelle description optionnelle.
     * @param string|null $codeGroupe Le nouveau code unique optionnel.
     * @return bool True si la modification est réussie.
     * @throws ElementNonTrouveException Si le groupe n'est pas trouvé.
     * @throws DoublonException Si le nouveau libellé ou code existe déjà pour un autre groupe.
     * @throws ValidationException Si les données sont invalides.
     * @throws PDOException En cas d'erreur de base de données.
     */
    public function modifierGroupeUtilisateur(int $idGroupe, string $libelle, ?string $description, ?string $codeGroupe = null): bool;

    /**
     * Supprime un groupe d'utilisateurs.
     * Vérifie si le groupe est utilisé par des utilisateurs avant suppression.
     *
     * @param int $idGroupe L'ID du groupe à supprimer.
     * @return bool True si la suppression est réussie.
     * @throws ElementNonTrouveException Si le groupe n'est pas trouvé.
     * @throws OperationImpossibleException Si le groupe est toujours utilisé.
     * @throws PDOException En cas d'erreur de base de données.
     */
    public function supprimerGroupeUtilisateur(int $idGroupe): bool;

    /**
     * Récupère un groupe d'utilisateurs par son ID.
     *
     * @param int $idGroupe L'ID du groupe.
     * @return array|null Les données du groupe ou null si non trouvé.
     */
    public function recupererGroupeUtilisateurParId(int $idGroupe): ?array;

    /**
     * Récupère un groupe d'utilisateurs par son code unique.
     *
     * @param string $codeGroupe Le code du groupe.
     * @return array|null Les données du groupe ou null si non trouvé.
     */
    public function recupererGroupeUtilisateurParCode(string $codeGroupe): ?array;

    /**
     * Liste tous les groupes d'utilisateurs.
     *
     * @return array La liste des groupes.
     */
    public function listerGroupesUtilisateur(): array;

    /**
     * Crée un nouveau type d'utilisateur.
     *
     * @param string $libelle Le libellé du type.
     * @param string|null $description La description optionnelle.
     * @param string|null $codeType Le code unique optionnel.
     * @return int L'ID du type créé.
     * @throws DoublonException Si le libellé ou le code existe déjà.
     * @throws ValidationException Si les données sont invalides.
     */
    public function creerTypeUtilisateur(string $libelle, ?string $description, ?string $codeType = null): int;

    /**
     * Modifie un type d'utilisateur existant.
     *
     * @param int $idType L'ID du type à modifier.
     * @param string $libelle Le nouveau libellé.
     * @param string|null $description La nouvelle description.
     * @param string|null $codeType Le nouveau code unique optionnel.
     * @return bool True si la modification est réussie.
     * @throws ElementNonTrouveException Si le type n'est pas trouvé.
     * @throws DoublonException Si le nouveau libellé ou code existe déjà.
     * @throws ValidationException Si les données sont invalides.
     */
    public function modifierTypeUtilisateur(int $idType, string $libelle, ?string $description, ?string $codeType = null): bool;

    /**
     * Supprime un type d'utilisateur.
     * Vérifie si le type est utilisé par des utilisateurs avant suppression.
     *
     * @param int $idType L'ID du type à supprimer.
     * @return bool True si la suppression est réussie.
     * @throws ElementNonTrouveException Si le type n'est pas trouvé.
     * @throws OperationImpossibleException Si le type est toujours utilisé.
     */
    public function supprimerTypeUtilisateur(int $idType): bool;

    /**
     * Récupère un type d'utilisateur par son ID.
     *
     * @param int $idType L'ID du type.
     * @return array|null Les données du type ou null si non trouvé.
     */
    public function recupererTypeUtilisateurParId(int $idType): ?array;

    /**
     * Récupère un type d'utilisateur par son code.
     *
     * @param string $codeType Le code du type.
     * @return array|null Les données du type ou null si non trouvé.
     */
    public function recupererTypeUtilisateurParCode(string $codeType): ?array;


    /**
     * Liste tous les types d'utilisateurs.
     *
     * @return array La liste des types.
     */
    public function listerTypesUtilisateur(): array;

    /**
     * Crée un nouveau niveau d'accès aux données.
     *
     * @param string $libelle Le libellé du niveau d'accès.
     * @param string|null $description La description optionnelle.
     * @param string|null $codeNiveauAcces Le code unique optionnel.
     * @return int L'ID du niveau d'accès créé.
     * @throws DoublonException Si le libellé ou le code existe déjà.
     * @throws ValidationException Si les données sont invalides.
     */
    public function creerNiveauAcces(string $libelle, ?string $description, ?string $codeNiveauAcces = null): int;

    /**
     * Modifie un niveau d'accès existant.
     *
     * @param int $idNiveau L'ID du niveau d'accès à modifier.
     * @param string $libelle Le nouveau libellé.
     * @param string|null $description La nouvelle description.
     * @param string|null $codeNiveauAcces Le nouveau code unique optionnel.
     * @return bool True si la modification est réussie.
     * @throws ElementNonTrouveException Si le niveau d'accès n'est pas trouvé.
     * @throws DoublonException Si le nouveau libellé ou code existe déjà.
     * @throws ValidationException Si les données sont invalides.
     */
    public function modifierNiveauAcces(int $idNiveau, string $libelle, ?string $description, ?string $codeNiveauAcces = null): bool;

    /**
     * Supprime un niveau d'accès.
     *
     * @param int $idNiveau L'ID du niveau d'accès à supprimer.
     * @return bool True si la suppression est réussie.
     * @throws ElementNonTrouveException Si le niveau d'accès n'est pas trouvé.
     * @throws OperationImpossibleException Si le niveau est utilisé.
     */
    public function supprimerNiveauAcces(int $idNiveau): bool;

    /**
     * Récupère un niveau d'accès par son ID.
     *
     * @param int $idNiveau L'ID du niveau d'accès.
     * @return array|null Les données du niveau d'accès ou null si non trouvé.
     */
    public function recupererNiveauAccesParId(int $idNiveau): ?array;

    /**
     * Récupère un niveau d'accès par son code.
     *
     * @param string $codeNiveauAcces Le code du niveau d'accès.
     * @return array|null Les données du niveau d'accès ou null si non trouvé.
     */
    public function recupererNiveauAccesParCode(string $codeNiveauAcces): ?array;

    /**
     * Liste tous les niveaux d'accès.
     *
     * @return array La liste des niveaux d'accès.
     */
    public function listerNiveauxAcces(): array;

    /**
     * Crée un nouveau traitement (permission granulaire).
     *
     * @param string $libelleTraitement Le libellé lisible du traitement.
     * @param string $codeTraitement Le code unique utilisé en programmation pour cette permission.
     * @return int L'ID du traitement créé.
     * @throws DoublonException Si le libellé ou le code du traitement existe déjà.
     * @throws ValidationException Si les données sont invalides.
     */
    public function creerTraitement(string $libelleTraitement, string $codeTraitement): int;

    /**
     * Modifie un traitement existant.
     *
     * @param int $idTraitement L'ID du traitement à modifier.
     * @param string $libelleTraitement Le nouveau libellé.
     * @param string $codeTraitement Le nouveau code unique.
     * @return bool True si la modification est réussie.
     * @throws ElementNonTrouveException Si le traitement n'est pas trouvé.
     * @throws DoublonException Si le nouveau libellé ou code existe déjà.
     * @throws ValidationException Si les données sont invalides.
     */
    public function modifierTraitement(int $idTraitement, string $libelleTraitement, string $codeTraitement): bool;

    /**
     * Supprime un traitement.
     * Vérifie si le traitement est rattaché à des groupes avant suppression.
     *
     * @param int $idTraitement L'ID du traitement à supprimer.
     * @return bool True si la suppression est réussie.
     * @throws ElementNonTrouveException Si le traitement n'est pas trouvé.
     * @throws OperationImpossibleException Si le traitement est toujours rattaché à des groupes.
     */
    public function supprimerTraitement(int $idTraitement): bool;

    /**
     * Récupère un traitement par son ID.
     *
     * @param int $idTraitement L'ID du traitement.
     * @return array|null Les données du traitement ou null si non trouvé.
     */
    public function recupererTraitementParId(int $idTraitement): ?array;

    /**
     * Récupère un traitement par son code unique.
     *
     * @param string $codeTraitement Le code du traitement.
     * @return array|null Les données du traitement ou null si non trouvé.
     */
    public function recupererTraitementParCode(string $codeTraitement): ?array;

    /**
     * Liste tous les traitements (permissions).
     *
     * @return array La liste des traitements.
     */
    public function listerTraitements(): array;

    /**
     * Attribue une permission (traitement) à un groupe d'utilisateurs.
     *
     * @param int $idGroupe L'ID du groupe.
     * @param int $idTraitement L'ID du traitement.
     * @return bool True si l'attribution est réussie ou si elle existait déjà.
     * @throws ElementNonTrouveException Si le groupe ou le traitement n'est pas trouvé.
     * @throws DoublonException Si la permission est déjà attribuée.
     */
    public function attribuerPermissionGroupe(int $idGroupe, int $idTraitement): bool;

    /**
     * Retire une permission (traitement) d'un groupe d'utilisateurs.
     *
     * @param int $idGroupe L'ID du groupe.
     * @param int $idTraitement L'ID du traitement.
     * @return bool True si le retrait est réussi.
     * @throws ElementNonTrouveException Si le groupe, le traitement ou le rattachement n'est pas trouvé.
     */
    public function retirerPermissionGroupe(int $idGroupe, int $idTraitement): bool;

    /**
     * Récupère toutes les permissions (traitements) attribuées à un groupe.
     *
     * @param int $idGroupe L'ID du groupe.
     * @return array Liste des objets/tableaux de traitements.
     * @throws ElementNonTrouveException Si le groupe n'est pas trouvé.
     */
    public function recupererPermissionsPourGroupe(int $idGroupe): array;

    /**
     * Récupère tous les groupes qui possèdent une permission (traitement) donnée.
     *
     * @param int $idTraitement L'ID du traitement.
     * @return array Liste des objets/tableaux de groupes.
     * @throws ElementNonTrouveException Si le traitement n'est pas trouvé.
     */
    public function recupererGroupesPourPermission(int $idTraitement): array;

    /**
     * Vérifie si un utilisateur possède une permission spécifique (via son groupe).
     *
     * @param string $numeroUtilisateur Le numéro de l'utilisateur.
     * @param string $codePermission Le code unique de la permission à vérifier.
     * @return bool True si l'utilisateur possède la permission, false sinon.
     * @throws ElementNonTrouveException Si l'utilisateur ou la permission (par code) n'est pas trouvé.
     */
    public function utilisateurPossedePermission(string $numeroUtilisateur, string $codePermission): bool;

    /**
     * Vérifie si un groupe d'utilisateurs possède une permission spécifique.
     *
     * @param int $idGroupeUtilisateur L'ID du groupe.
     * @param string $codePermission Le code unique de la permission à vérifier.
     * @return bool True si le groupe possède la permission, false sinon.
     * @throws ElementNonTrouveException Si le groupe ou la permission (par code) n'est pas trouvé.
     */
    public function groupePossedePermission(int $idGroupeUtilisateur, string $codePermission): bool;

    /**
     * Récupère la liste des codes de permission pour un utilisateur donné (via son groupe).
     *
     * @param string $numeroUtilisateur Le numéro de l'utilisateur.
     * @return array Liste des codes de permission (strings).
     * @throws ElementNonTrouveException Si l'utilisateur n'est pas trouvé.
     */
    public function getPermissionsPourUtilisateur(string $numeroUtilisateur): array;
}