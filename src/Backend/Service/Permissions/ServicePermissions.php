<?php

namespace App\Backend\Service\Permissions;

use PDO;
use App\Backend\Model\GroupeUtilisateur;
use App\Backend\Model\TypeUtilisateur;
use App\Backend\Model\NiveauAccesDonne;
use App\Backend\Model\Traitement;
use App\Backend\Model\Rattacher;
use App\Backend\Model\Utilisateur;
use App\Backend\Service\SupervisionAdmin\ServiceSupervisionAdminInterface;
use App\Backend\Exception\ElementNonTrouveException;
use App\Backend\Exception\OperationImpossibleException;
use App\Backend\Exception\DoublonException;

class ServicePermissions implements ServicePermissionsInterface
{
    private PDO $db; // Ajout de la propriété PDO pour l'accès direct à la base de données
    private GroupeUtilisateur $groupeUtilisateurModel;
    private TypeUtilisateur $typeUtilisateurModel;
    private NiveauAccesDonne $niveauAccesDonneModel;
    private Traitement $traitementModel;
    private Rattacher $rattacherModel;
    private Utilisateur $utilisateurModel;
    private ServiceSupervisionAdminInterface $supervisionService;

    public function __construct(
        PDO $db, // <<<<<<< Ajout de l'injection de l'instance PDO
        GroupeUtilisateur $groupeUtilisateurModel,
        TypeUtilisateur $typeUtilisateurModel,
        NiveauAccesDonne $niveauAccesDonneModel,
        Traitement $traitementModel,
        Rattacher $rattacherModel,
        Utilisateur $utilisateurModel,
        ServiceSupervisionAdminInterface $supervisionService
    ) {
        $this->db = $db; // <<<<<<< Initialisation de la propriété PDO
        $this->groupeUtilisateurModel = $groupeUtilisateurModel;
        $this->typeUtilisateurModel = $typeUtilisateurModel;
        $this->niveauAccesDonneModel = $niveauAccesDonneModel;
        $this->traitementModel = $traitementModel;
        $this->rattacherModel = $rattacherModel;
        $this->utilisateurModel = $utilisateurModel;
        $this->supervisionService = $supervisionService;
    }

    /**
     * Récupère la liste des permissions (traitements) et leurs détails
     * pour un utilisateur donné, incluant les URLs, parents et icônes,
     * optimisée pour la construction du menu.
     *
     * @param string $userId L'ID de l'utilisateur.
     * @return array Un tableau associatif des traitements accessibles par l'utilisateur.
     */
    public function getPermissionsForUser(string $userId): array
    {
        $permissions = [];
        try {
            // 1. Récupérer l'ID de groupe de l'utilisateur depuis la table 'utilisateur' [cite: uploaded:manueld-aho/gestionmysoutenance/GestionMySoutenance-24b2b01d2b765035009d2a7af2249c0f12c911ab/mysoutenance.sql]
            $stmtUser = $this->db->prepare("SELECT id_groupe_utilisateur FROM utilisateur WHERE numero_utilisateur = :userId");
            $stmtUser->bindParam(':userId', $userId);
            $stmtUser->execute();
            $user = $stmtUser->fetch(PDO::FETCH_ASSOC);

            if ($user && isset($user['id_groupe_utilisateur'])) {
                $groupId = $user['id_groupe_utilisateur'];

                // 2. Récupérer tous les traitements associés à ce groupe via la table 'rattacher', [cite: uploaded:manueld-aho/gestionmysoutenance/GestionMySoutenance-24b2b01d2b765035009d2a7af2249c0f12c911ab/mysoutenance.sql]
                // et toutes les informations pertinentes de la table 'traitement' [cite: uploaded:manueld-aho/gestionmysoutenance/GestionMySoutenance-24b2b01d2b765035009d2a7af2249c0f12c911ab/mysoutenance.sql]
                // pour la construction du menu (libellé, url, parent, icône).
                // L'ordre est important pour faciliter la construction de l'arbre du menu.
                $stmtPermissions = $this->db->prepare("
                    SELECT
                        t.id_traitement,
                        t.libelle_traitement,
                        t.url_associee,
                        t.id_parent_traitement,
                        t.icone_class
                    FROM
                        traitement t
                    JOIN
                        rattacher r ON t.id_traitement = r.id_traitement
                    WHERE
                        r.id_groupe_utilisateur = :groupId
                    ORDER BY
                        t.id_parent_traitement ASC, t.libelle_traitement ASC
                ");
                $stmtPermissions->bindParam(':groupId', $groupId);
                $stmtPermissions->execute();
                $permissions = $stmtPermissions->fetchAll(PDO::FETCH_ASSOC);
            }
        } catch (\PDOException $e) {
            // Journalisation de l'erreur pour le débogage. En production, utilisez un système de log approprié.
            error_log("Erreur PDO dans ServicePermissions::getPermissionsForUser: " . $e->getMessage());
            // Il est préférable de lancer une exception plus spécifique pour la couche métier.
            throw new OperationImpossibleException("Erreur lors de la récupération des permissions du menu: " . $e->getMessage());
        }
        return $permissions;
    }

    // --- Les autres méthodes de votre classe ServicePermissions restent inchangées ci-dessous ---

    public function creerGroupeUtilisateur(string $idGroupeUtilisateur, string $libelleGroupeUtilisateur): bool
    {
        $this->groupeUtilisateurModel->commencerTransaction();
        try {
            $data = [
                'id_groupe_utilisateur' => $idGroupeUtilisateur,
                'libelle_groupe_utilisateur' => $libelleGroupeUtilisateur
            ];
            $success = $this->groupeUtilisateurModel->creer($data);
            if (!$success) {
                throw new OperationImpossibleException("Échec de la création du groupe utilisateur.");
            }
            $this->groupeUtilisateurModel->validerTransaction();
            $this->supervisionService->enregistrerAction(
                $_SESSION['user_id'] ?? 'SYSTEM',
                'CREATION_GROUPE_UTILISATEUR',
                "Groupe utilisateur '{$idGroupeUtilisateur}' créé.",
                $idGroupeUtilisateur,
                'GroupeUtilisateur'
            );
            return true;
        } catch (\Exception $e) {
            $this->groupeUtilisateurModel->annulerTransaction();
            throw $e;
        }
    }

    public function modifierGroupeUtilisateur(string $idGroupeUtilisateur, array $donnees): bool
    {
        if (!$this->groupeUtilisateurModel->trouverParIdentifiant($idGroupeUtilisateur)) {
            throw new ElementNonTrouveException("Groupe utilisateur '{$idGroupeUtilisateur}' non trouvé.");
        }
        $success = $this->groupeUtilisateurModel->mettreAJourParIdentifiant($idGroupeUtilisateur, $donnees);
        if ($success) {
            $this->supervisionService->enregistrerAction(
                $_SESSION['user_id'] ?? 'SYSTEM',
                'MODIF_GROUPE_UTILISATEUR',
                "Groupe utilisateur '{$idGroupeUtilisateur}' mis à jour.",
                $idGroupeUtilisateur,
                'GroupeUtilisateur'
            );
        }
        return $success;
    }

    public function supprimerGroupeUtilisateur(string $idGroupeUtilisateur): bool
    {
        if (!$this->groupeUtilisateurModel->trouverParIdentifiant($idGroupeUtilisateur)) {
            throw new ElementNonTrouveException("Groupe utilisateur '{$idGroupeUtilisateur}' non trouvé.");
        }
        if ($this->utilisateurModel->compterParCritere(['id_groupe_utilisateur' => $idGroupeUtilisateur]) > 0) {
            throw new OperationImpossibleException("Impossible de supprimer le groupe : des utilisateurs y sont encore rattachés.");
        }
        if ($this->rattacherModel->compterParCritere(['id_groupe_utilisateur' => $idGroupeUtilisateur]) > 0) {
            throw new OperationImpossibleException("Impossible de supprimer le groupe : des permissions lui sont encore rattachées.");
        }
        $success = $this->groupeUtilisateurModel->supprimerParIdentifiant($idGroupeUtilisateur);
        if ($success) {
            $this->supervisionService->enregistrerAction(
                $_SESSION['user_id'] ?? 'SYSTEM',
                'SUPPRESSION_GROUPE_UTILISATEUR',
                "Groupe utilisateur '{$idGroupeUtilisateur}' supprimé.",
                $idGroupeUtilisateur,
                'GroupeUtilisateur'
            );
        }
        return $success;
    }

    public function listerGroupesUtilisateur(): array
    {
        return $this->groupeUtilisateurModel->trouverTout();
    }

    public function recupererGroupeUtilisateurParId(string $idGroupeUtilisateur): ?array
    {
        return $this->groupeUtilisateurModel->trouverParIdentifiant($idGroupeUtilisateur);
    }

    public function recupererGroupeUtilisateurParCode(string $codeGroupe): ?array
    {
        return $this->groupeUtilisateurModel->trouverUnParCritere(['id_groupe_utilisateur' => $codeGroupe]);
    }

    public function creerTypeUtilisateur(string $idTypeUtilisateur, string $libelleTypeUtilisateur): bool
    {
        $data = [
            'id_type_utilisateur' => $idTypeUtilisateur,
            'libelle_type_utilisateur' => $libelleTypeUtilisateur
        ];
        $success = $this->typeUtilisateurModel->creer($data);
        if ($success) {
            $this->supervisionService->enregistrerAction(
                $_SESSION['user_id'] ?? 'SYSTEM',
                'CREATION_TYPE_UTILISATEUR',
                "Type d'utilisateur '{$idTypeUtilisateur}' créé.",
                $idTypeUtilisateur,
                'TypeUtilisateur'
            );
        }
        return $success;
    }

    public function modifierTypeUtilisateur(string $idTypeUtilisateur, array $donnees): bool
    {
        if (!$this->typeUtilisateurModel->trouverParIdentifiant($idTypeUtilisateur)) {
            throw new ElementNonTrouveException("Type d'utilisateur '{$idTypeUtilisateur}' non trouvé.");
        }
        $success = $this->typeUtilisateurModel->mettreAJourParIdentifiant($idTypeUtilisateur, $donnees);
        if ($success) {
            $this->supervisionService->enregistrerAction(
                $_SESSION['user_id'] ?? 'SYSTEM',
                'MODIF_TYPE_UTILISATEUR',
                "Type d'utilisateur '{$idTypeUtilisateur}' mis à jour.",
                $idTypeUtilisateur,
                'TypeUtilisateur'
            );
        }
        return $success;
    }

    public function supprimerTypeUtilisateur(string $idTypeUtilisateur): bool
    {
        if (!$this->typeUtilisateurModel->trouverParIdentifiant($idTypeUtilisateur)) {
            throw new ElementNonTrouveException("Type d'utilisateur '{$idTypeUtilisateur}' non trouvé.");
        }
        if ($this->utilisateurModel->compterParCritere(['id_type_utilisateur' => $idTypeUtilisateur]) > 0) {
            throw new OperationImpossibleException("Impossible de supprimer le type d'utilisateur : des utilisateurs y sont encore rattachés.");
        }
        $success = $this->typeUtilisateurModel->supprimerParIdentifiant($idTypeUtilisateur);
        if ($success) {
            $this->supervisionService->enregistrerAction(
                $_SESSION['user_id'] ?? 'SYSTEM',
                'SUPPRESSION_TYPE_UTILISATEUR',
                "Type d'utilisateur '{$idTypeUtilisateur}' supprimé.",
                $idTypeUtilisateur,
                'TypeUtilisateur'
            );
        }
        return $success;
    }

    public function listerTypesUtilisateur(): array
    {
        return $this->typeUtilisateurModel->trouverTout();
    }

    public function recupererTypeUtilisateurParId(string $idTypeUtilisateur): ?array
    {
        return $this->typeUtilisateurModel->trouverParIdentifiant($idTypeUtilisateur);
    }

    public function recupererTypeUtilisateurParCode(string $codeType): ?array
    {
        return $this->typeUtilisateurModel->trouverUnParCritere(['id_type_utilisateur' => $codeType]);
    }

    public function creerNiveauAcces(string $idNiveauAcces, string $libelleNiveauAcces): bool
    {
        $data = [
            'id_niveau_acces_donne' => $idNiveauAcces,
            'libelle_niveau_acces_donne' => $libelleNiveauAcces
        ];
        $success = $this->niveauAccesDonneModel->creer($data);
        if ($success) {
            $this->supervisionService->enregistrerAction(
                $_SESSION['user_id'] ?? 'SYSTEM',
                'CREATION_NIVEAU_ACCES',
                "Niveau d'accès '{$idNiveauAcces}' créé.",
                $idNiveauAcces,
                'NiveauAccesDonne'
            );
        }
        return $success;
    }

    public function modifierNiveauAcces(string $idNiveauAcces, array $donnees): bool
    {
        if (!$this->niveauAccesDonneModel->trouverParIdentifiant($idNiveauAcces)) {
            throw new ElementNonTrouveException("Niveau d'accès '{$idNiveauAcces}' non trouvé.");
        }
        $success = $this->niveauAccesDonneModel->mettreAJourParIdentifiant($idNiveauAcces, $donnees);
        if ($success) {
            $this->supervisionService->enregistrerAction(
                $_SESSION['user_id'] ?? 'SYSTEM',
                'MODIF_NIVEAU_ACCES',
                "Niveau d'accès '{$idNiveauAcces}' mis à jour.",
                $idNiveauAcces,
                'NiveauAccesDonne'
            );
        }
        return $success;
    }

    public function supprimerNiveauAcces(string $idNiveauAcces): bool
    {
        if (!$this->niveauAccesDonneModel->trouverParIdentifiant($idNiveauAcces)) {
            throw new ElementNonTrouveException("Niveau d'accès '{$idNiveauAcces}' non trouvé.");
        }
        if ($this->utilisateurModel->compterParCritere(['id_niveau_acces_donne' => $idNiveauAcces]) > 0) {
            throw new OperationImpossibleException("Impossible de supprimer le niveau d'accès : des utilisateurs y sont encore rattachés.");
        }
        $success = $this->niveauAccesDonneModel->supprimerParIdentifiant($idNiveauAcces);
        if ($success) {
            $this->supervisionService->enregistrerAction(
                $_SESSION['user_id'] ?? 'SYSTEM',
                'SUPPRESSION_NIVEAU_ACCES',
                "Niveau d'accès '{$idNiveauAcces}' supprimé.",
                $idNiveauAcces,
                'NiveauAccesDonne'
            );
        }
        return $success;
    }

    public function listerNiveauxAcces(): array
    {
        return $this->niveauAccesDonneModel->trouverTout();
    }

    public function recupererNiveauAccesParId(string $idNiveauAcces): ?array
    {
        return $this->niveauAccesDonneModel->trouverParIdentifiant($idNiveauAcces);
    }

    public function recupererNiveauAccesParCode(string $codeNiveau): ?array
    {
        return $this->niveauAccesDonneModel->trouverUnParCritere(['id_niveau_acces_donne' => $codeNiveau]);
    }

    public function creerTraitement(string $idTraitement, string $libelleTraitement): bool
    {
        $data = [
            'id_traitement' => $idTraitement,
            'libelle_traitement' => $libelleTraitement
        ];
        $success = $this->traitementModel->creer($data);
        if ($success) {
            $this->supervisionService->enregistrerAction(
                $_SESSION['user_id'] ?? 'SYSTEM',
                'CREATION_TRAITEMENT',
                "Traitement '{$idTraitement}' créé.",
                $idTraitement,
                'Traitement'
            );
        }
        return $success;
    }

    public function modifierTraitement(string $idTraitement, array $donnees): bool
    {
        if (!$this->traitementModel->trouverParIdentifiant($idTraitement)) {
            throw new ElementNonTrouveException("Traitement '{$idTraitement}' non trouvé.");
        }
        $success = $this->traitementModel->mettreAJourParIdentifiant($idTraitement, $donnees);
        if ($success) {
            $this->supervisionService->enregistrerAction(
                $_SESSION['user_id'] ?? 'SYSTEM',
                'MODIF_TRAITEMENT',
                "Traitement '{$idTraitement}' mis à jour.",
                $idTraitement,
                'Traitement'
            );
        }
        return $success;
    }

    public function supprimerTraitement(string $idTraitement): bool
    {
        if (!$this->traitementModel->trouverParIdentifiant($idTraitement)) {
            throw new ElementNonTrouveException("Traitement '{$idTraitement}' non trouvé.");
        }
        if ($this->rattacherModel->compterParCritere(['id_traitement' => $idTraitement]) > 0) {
            throw new OperationImpossibleException("Impossible de supprimer le traitement : des rattachements existent.");
        }
        $success = $this->traitementModel->supprimerParIdentifiant($idTraitement);
        if ($success) {
            $this->supervisionService->enregistrerAction(
                $_SESSION['user_id'] ?? 'SYSTEM',
                'SUPPRESSION_TRAITEMENT',
                "Traitement '{$idTraitement}' supprimé.",
                $idTraitement,
                'Traitement'
            );
        }
        return $success;
    }

    public function listerTraitements(): array
    {
        return $this->traitementModel->trouverTout();
    }

    public function recupererTraitementParId(string $idTraitement): ?array
    {
        return $this->traitementModel->trouverParIdentifiant($idTraitement);
    }

    public function recupererTraitementParCode(string $codeTraitement): ?array
    {
        return $this->traitementModel->trouverUnParCritere(['id_traitement' => $codeTraitement]);
    }

    public function attribuerPermissionGroupe(string $idGroupeUtilisateur, string $idTraitement): bool
    {
        $data = [
            'id_groupe_utilisateur' => $idGroupeUtilisateur,
            'id_traitement' => $idTraitement
        ];
        $success = $this->rattacherModel->creer($data);
        if ($success) {
            $this->supervisionService->enregistrerAction(
                $_SESSION['user_id'] ?? 'SYSTEM',
                'ATTRIB_PERM_GROUPE',
                "Permission '{$idTraitement}' attribuée au groupe '{$idGroupeUtilisateur}'."
            );
        }
        return $success;
    }

    public function retirerPermissionGroupe(string $idGroupeUtilisateur, string $idTraitement): bool
    {
        $success = $this->rattacherModel->supprimerParClesInternes([
            'id_groupe_utilisateur' => $idGroupeUtilisateur,
            'id_traitement' => $idTraitement
        ]);
        if ($success) {
            $this->supervisionService->enregistrerAction(
                $_SESSION['user_id'] ?? 'SYSTEM',
                'RETRAIT_PERM_GROUPE',
                "Permission '{$idTraitement}' retirée du groupe '{$idGroupeUtilisateur}'."
            );
        }
        return $success;
    }

    public function recupererPermissionsPourGroupe(string $idGroupeUtilisateur): array
    {
        // Cette méthode semble déjà être une version simplifiée de récupération de permissions
        // sans les détails des traitements. Nous allons conserver `getPermissionsForUser`
        // pour la logique de menu spécifique.
        if (!$this->groupeUtilisateurModel->trouverParIdentifiant($idGroupeUtilisateur)) {
            throw new ElementNonTrouveException("Groupe utilisateur '{$idGroupeUtilisateur}' non trouvé.");
        }
        $rattachements = $this->rattacherModel->trouverParCritere(['id_groupe_utilisateur' => $idGroupeUtilisateur], ['id_traitement']);
        return array_column($rattachements, 'id_traitement');
    }

    public function recupererGroupesPourPermission(string $idTraitement): array
    {
        if (!$this->traitementModel->trouverParIdentifiant($idTraitement)) {
            throw new ElementNonTrouveException("Traitement '{$idTraitement}' non trouvé.");
        }
        $rattachements = $this->rattacherModel->trouverParCritere(['id_traitement' => $idTraitement], ['id_groupe_utilisateur']);
        return array_column($rattachements, 'id_groupe_utilisateur');
    }

    public function utilisateurPossedePermission(string $permissionCode): bool
    {
        return isset($_SESSION['user_permissions']) && in_array($permissionCode, $_SESSION['user_permissions']);
    }

    public function groupePossedePermission(string $idGroupeUtilisateur, string $permissionCode): bool
    {
        if (!$this->groupeUtilisateurModel->trouverParIdentifiant($idGroupeUtilisateur)) {
            throw new ElementNonTrouveException("Groupe utilisateur '{$idGroupeUtilisateur}' non trouvé.");
        }
        return $this->rattacherModel->trouverRattachementParCles($idGroupeUtilisateur, $permissionCode) !== null;
    }
}
