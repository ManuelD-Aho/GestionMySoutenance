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
    private GroupeUtilisateur $groupeUtilisateurModel;
    private TypeUtilisateur $typeUtilisateurModel;
    private NiveauAccesDonne $niveauAccesDonneModel;
    private Traitement $traitementModel;
    private Rattacher $rattacherModel;
    private Utilisateur $utilisateurModel;
    private ServiceSupervisionAdminInterface $supervisionService;

    public function __construct(PDO $db, ServiceSupervisionAdminInterface $supervisionService)
    {
        $this->groupeUtilisateurModel = new GroupeUtilisateur($db);
        $this->typeUtilisateurModel = new TypeUtilisateur($db);
        $this->niveauAccesDonneModel = new NiveauAccesDonne($db);
        $this->traitementModel = new Traitement($db);
        $this->rattacherModel = new Rattacher($db);
        $this->utilisateurModel = new Utilisateur($db);
        $this->supervisionService = $supervisionService;
    }

    public function creerGroupeUtilisateur(string $idGroupeUtilisateur, string $libelleGroupeUtilisateur): bool
    {
        $data = ['id_groupe_utilisateur' => $idGroupeUtilisateur, 'libelle_groupe_utilisateur' => $libelleGroupeUtilisateur];
        return (bool) $this->groupeUtilisateurModel->creer($data);
    }

    public function modifierGroupeUtilisateur(string $idGroupeUtilisateur, array $donnees): bool
    {
        if (!$this->groupeUtilisateurModel->trouverParIdentifiant($idGroupeUtilisateur)) {
            throw new ElementNonTrouveException("Groupe '{$idGroupeUtilisateur}' non trouvé.");
        }
        return $this->groupeUtilisateurModel->mettreAJourParIdentifiant($idGroupeUtilisateur, $donnees);
    }

    public function supprimerGroupeUtilisateur(string $idGroupeUtilisateur): bool
    {
        if ($this->utilisateurModel->compterParCritere(['id_groupe_utilisateur' => $idGroupeUtilisateur]) > 0) {
            throw new OperationImpossibleException("Impossible de supprimer le groupe : des utilisateurs y sont rattachés.");
        }
        $this->rattacherModel->supprimerParClesInternes(['id_groupe_utilisateur' => $idGroupeUtilisateur]);
        return $this->groupeUtilisateurModel->supprimerParIdentifiant($idGroupeUtilisateur);
    }

    public function listerGroupesUtilisateur(): array
    {
        return $this->groupeUtilisateurModel->trouverTout();
    }

    public function recupererGroupeUtilisateurParId(string $idGroupeUtilisateur): ?array
    {
        return $this->groupeUtilisateurModel->trouverParIdentifiant($idGroupeUtilisateur);
    }

    public function creerTypeUtilisateur(string $idTypeUtilisateur, string $libelleTypeUtilisateur): bool
    {
        return (bool) $this->typeUtilisateurModel->creer(['id_type_utilisateur' => $idTypeUtilisateur, 'libelle_type_utilisateur' => $libelleTypeUtilisateur]);
    }

    public function modifierTypeUtilisateur(string $idTypeUtilisateur, array $donnees): bool
    {
        if (!$this->typeUtilisateurModel->trouverParIdentifiant($idTypeUtilisateur)) {
            throw new ElementNonTrouveException("Type d'utilisateur '{$idTypeUtilisateur}' non trouvé.");
        }
        return $this->typeUtilisateurModel->mettreAJourParIdentifiant($idTypeUtilisateur, $donnees);
    }

    public function supprimerTypeUtilisateur(string $idTypeUtilisateur): bool
    {
        if ($this->utilisateurModel->compterParCritere(['id_type_utilisateur' => $idTypeUtilisateur]) > 0) {
            throw new OperationImpossibleException("Impossible de supprimer le type : des utilisateurs y sont rattachés.");
        }
        return $this->typeUtilisateurModel->supprimerParIdentifiant($idTypeUtilisateur);
    }

    public function listerTypesUtilisateur(): array
    {
        return $this->typeUtilisateurModel->trouverTout();
    }

    public function creerNiveauAcces(string $idNiveauAcces, string $libelleNiveauAcces): bool
    {
        return (bool) $this->niveauAccesDonneModel->creer(['id_niveau_acces_donne' => $idNiveauAcces, 'libelle_niveau_acces_donne' => $libelleNiveauAcces]);
    }

    public function modifierNiveauAcces(string $idNiveauAcces, array $donnees): bool
    {
        if (!$this->niveauAccesDonneModel->trouverParIdentifiant($idNiveauAcces)) {
            throw new ElementNonTrouveException("Niveau d'accès '{$idNiveauAcces}' non trouvé.");
        }
        return $this->niveauAccesDonneModel->mettreAJourParIdentifiant($idNiveauAcces, $donnees);
    }

    public function supprimerNiveauAcces(string $idNiveauAcces): bool
    {
        if ($this->utilisateurModel->compterParCritere(['id_niveau_acces_donne' => $idNiveauAcces]) > 0) {
            throw new OperationImpossibleException("Impossible de supprimer le niveau d'accès : des utilisateurs y sont rattachés.");
        }
        return $this->niveauAccesDonneModel->supprimerParIdentifiant($idNiveauAcces);
    }

    public function listerNiveauxAcces(): array
    {
        return $this->niveauAccesDonneModel->trouverTout();
    }

    public function creerTraitement(string $idTraitement, string $libelleTraitement): bool
    {
        return (bool) $this->traitementModel->creer(['id_traitement' => $idTraitement, 'libelle_traitement' => $libelleTraitement]);
    }

    public function modifierTraitement(string $idTraitement, array $donnees): bool
    {
        if (!$this->traitementModel->trouverParIdentifiant($idTraitement)) {
            throw new ElementNonTrouveException("Traitement '{$idTraitement}' non trouvé.");
        }
        return $this->traitementModel->mettreAJourParIdentifiant($idTraitement, $donnees);
    }

    public function supprimerTraitement(string $idTraitement): bool
    {
        if ($this->rattacherModel->compterParCritere(['id_traitement' => $idTraitement]) > 0) {
            throw new OperationImpossibleException("Impossible de supprimer le traitement : il est rattaché à des groupes.");
        }
        return $this->traitementModel->supprimerParIdentifiant($idTraitement);
    }

    public function listerTraitements(): array
    {
        return $this->traitementModel->trouverTout();
    }

    public function attribuerPermissionGroupe(string $idGroupeUtilisateur, string $idTraitement): bool
    {
        if (!$this->groupeUtilisateurModel->trouverParIdentifiant($idGroupeUtilisateur) || !$this->traitementModel->trouverParIdentifiant($idTraitement)) {
            throw new ElementNonTrouveException("Groupe ou traitement non trouvé.");
        }
        return (bool) $this->rattacherModel->creer(['id_groupe_utilisateur' => $idGroupeUtilisateur, 'id_traitement' => $idTraitement]);
    }

    public function retirerPermissionGroupe(string $idGroupeUtilisateur, string $idTraitement): bool
    {
        return $this->rattacherModel->supprimerParClesInternes(['id_groupe_utilisateur' => $idGroupeUtilisateur, 'id_traitement' => $idTraitement]);
    }

    public function recupererPermissionsPourGroupe(string $idGroupeUtilisateur): array
    {
        $rattachements = $this->rattacherModel->trouverParCritere(['id_groupe_utilisateur' => $idGroupeUtilisateur]);
        return array_column($rattachements, 'id_traitement');
    }

    public function utilisateurPossedePermission(string $permissionCode): bool
    {
        return isset($_SESSION['user_permissions']) && in_array($permissionCode, $_SESSION['user_permissions']);
    }
}