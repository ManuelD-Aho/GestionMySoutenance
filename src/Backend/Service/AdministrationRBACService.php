<?php

declare(strict_types=1);

namespace App\Backend\Service;

use PDO;
use App\Backend\Model\GroupeUtilisateur;
use App\Backend\Model\Traitement;
use App\Backend\Model\Rattacher;
use App\Backend\Service\Interface\AdministrationRBACServiceInterface;
use App\Backend\Service\Interface\PermissionsServiceInterface;
use App\Backend\Service\Interface\AuditServiceInterface;
use App\Backend\Exception\DoublonException;
use App\Backend\Exception\ElementNonTrouveException;
use App\Backend\Exception\OperationImpossibleException;

class AdministrationRBACService implements AdministrationRBACServiceInterface
{
    private PDO $pdo;
    private GroupeUtilisateur $groupeModel;
    private Traitement $traitementModel;
    private Rattacher $rattacherModel;
    private PermissionsServiceInterface $permissionsService;
    private AuditServiceInterface $auditService;
    private string $currentUserLogin;

    public function __construct(
        PDO $pdo,
        GroupeUtilisateur $groupeModel,
        Traitement $traitementModel,
        Rattacher $rattacherModel,
        PermissionsServiceInterface $permissionsService,
        AuditServiceInterface $auditService
    ) {
        $this->pdo = $pdo;
        $this->groupeModel = $groupeModel;
        $this->traitementModel = $traitementModel;
        $this->rattacherModel = $rattacherModel;
        $this->permissionsService = $permissionsService;
        $this->auditService = $auditService;
        $this->currentUserLogin = $_SESSION['user_id'] ?? 'SYSTEM_ADMIN';
    }

    public function creerGroupe(string $idGroupe, string $libelle): string
    {
        if ($this->groupeModel->compterParCritere(['id_groupe_utilisateur' => $idGroupe]) > 0) {
            throw new DoublonException("Un groupe avec l'ID '{$idGroupe}' existe déjà.");
        }
        if ($this->groupeModel->compterParCritere(['libelle_groupe_utilisateur' => $libelle]) > 0) {
            throw new DoublonException("Un groupe avec le libellé '{$libelle}' existe déjà.");
        }

        $this->groupeModel->creer([
            'id_groupe_utilisateur' => $idGroupe,
            'libelle_groupe_utilisateur' => $libelle
        ]);

        $this->auditService->enregistrerAction($this->currentUserLogin, 'CREATION_GROUPE_UTILISATEUR', $idGroupe, 'GroupeUtilisateur');

        return $idGroupe;
    }

    public function creerTraitement(string $idTraitement, string $libelle, ?string $idParentTraitement, ?string $iconeClass, ?string $urlAssociee): string
    {
        if ($this->traitementModel->compterParCritere(['id_traitement' => $idTraitement]) > 0) {
            throw new DoublonException("Un traitement avec l'ID '{$idTraitement}' existe déjà.");
        }
        if ($idParentTraitement && $this->traitementModel->compterParCritere(['id_traitement' => $idParentTraitement]) === 0) {
            throw new ElementNonTrouveException("Le traitement parent '{$idParentTraitement}' n'existe pas.");
        }

        $this->traitementModel->creer([
            'id_traitement' => $idTraitement,
            'libelle_traitement' => $libelle,
            'id_parent_traitement' => $idParentTraitement,
            'icone_class' => $iconeClass,
            'url_associee' => $urlAssociee
        ]);

        $this->auditService->enregistrerAction($this->currentUserLogin, 'CREATION_TRAITEMENT', $idTraitement, 'Traitement');

        return $idTraitement;
    }

    public function assignerTraitementAGroupe(string $idTraitement, string $idGroupe): bool
    {
        if ($this->traitementModel->compterParCritere(['id_traitement' => $idTraitement]) === 0) {
            throw new ElementNonTrouveException("Le traitement '{$idTraitement}' n'existe pas.");
        }
        if ($this->groupeModel->compterParCritere(['id_groupe_utilisateur' => $idGroupe]) === 0) {
            throw new ElementNonTrouveException("Le groupe '{$idGroupe}' n'existe pas.");
        }
        if ($this->rattacherModel->compterParCritere(['id_traitement' => $idTraitement, 'id_groupe_utilisateur' => $idGroupe]) > 0) {
            return true; // L'assignation existe déjà, l'opération est considérée comme réussie (idempotence).
        }

        $this->pdo->beginTransaction();
        try {
            $this->rattacherModel->creer([
                'id_groupe_utilisateur' => $idGroupe,
                'id_traitement' => $idTraitement
            ]);

            // Synchroniser immédiatement les sessions des utilisateurs de ce groupe.
            $this->synchroniserSessionsPourGroupe($idGroupe);

            $this->pdo->commit();
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw new OperationImpossibleException("Échec de l'assignation du traitement : " . $e->getMessage());
        }

        $this->auditService->enregistrerAction($this->currentUserLogin, 'ATTRIB_PERM_GROUPE', $idGroupe, 'GroupeUtilisateur', ['traitement_ajoute' => $idTraitement]);

        return true;
    }

    public function retirerTraitementDeGroupe(string $idTraitement, string $idGroupe): bool
    {
        $this->pdo->beginTransaction();
        try {
            $resultat = $this->rattacherModel->supprimerParCritere([
                'id_traitement' => $idTraitement,
                'id_groupe_utilisateur' => $idGroupe
            ]);

            if ($resultat > 0) {
                // Synchroniser immédiatement les sessions des utilisateurs de ce groupe.
                $this->synchroniserSessionsPourGroupe($idGroupe);
            }

            $this->pdo->commit();
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw new OperationImpossibleException("Échec du retrait du traitement : " . $e->getMessage());
        }

        if ($resultat > 0) {
            $this->auditService->enregistrerAction($this->currentUserLogin, 'RETRAIT_PERM_GROUPE', $idGroupe, 'GroupeUtilisateur', ['traitement_retire' => $idTraitement]);
        }

        return $resultat > 0;
    }

    public function getTraitementsPourGroupe(string $idGroupe): array
    {
        $rattachements = $this->rattacherModel->trouverParCritere(['id_groupe_utilisateur' => $idGroupe]);
        return array_column($rattachements, 'id_traitement');
    }

    /**
     * Méthode privée pour synchroniser les sessions après une modification de droits.
     * C'est ici que les deux services collaborent.
     */
    private function synchroniserSessionsPourGroupe(string $idGroupe): void
    {
        $sql = "SELECT numero_utilisateur FROM utilisateur WHERE id_groupe_utilisateur = :id_groupe";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id_groupe' => $idGroupe]);
        $utilisateurs = $stmt->fetchAll(PDO::FETCH_COLUMN);

        foreach ($utilisateurs as $numeroUtilisateur) {
            $this->permissionsService->synchroniserPermissionsPourSessionsActives($numeroUtilisateur);
        }

        $this->auditService->enregistrerAction($this->currentUserLogin, 'SYNCHRONISATION_RBAC', $idGroupe, 'GroupeUtilisateur');
    }
}