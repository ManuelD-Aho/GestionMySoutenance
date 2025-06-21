<?php
namespace App\Backend\Service\Permissions;

use PDO;
use App\Backend\Model\GroupeUtilisateur;
use App\Backend\Model\TypeUtilisateur;
use App\Backend\Model\NiveauAccesDonne;
use App\Backend\Model\Traitement;
use App\Backend\Model\Rattacher;
use App\Backend\Model\Utilisateur; // Pour des opérations liées aux utilisateurs si nécessaire
use App\Backend\Service\SupervisionAdmin\ServiceSupervisionAdmin; // Pour journalisation
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
    private Utilisateur $utilisateurModel; // Utilisé pour récupérer l'utilisateur lié à la session
    private ServiceSupervisionAdmin $supervisionService;

    public function __construct(
        PDO $db,
        ServiceSupervisionAdmin $supervisionService,
        // Ces modèles pourraient être passés directement via le Container si configuré,
        // ou instanciés ici si ce service est toujours le premier à les utiliser.
        // Pour l'exercice, nous les instancions ici.
        // Utilisez les objets des modèles si déjà injectés par un Container plus haut.
        // Si c'est pour des tests unitaires ou des cas où ils sont déjà disponibles,
        // il est préférable de les demander en paramètre du constructeur.
        Utilisateur $utilisateurModel = null,
        GroupeUtilisateur $groupeUtilisateurModel = null,
        TypeUtilisateur $typeUtilisateurModel = null
    ) {
        $this->groupeUtilisateurModel = $groupeUtilisateurModel ?? new GroupeUtilisateur($db);
        $this->typeUtilisateurModel = $typeUtilisateurModel ?? new TypeUtilisateur($db);
        $this->niveauAccesDonneModel = new NiveauAccesDonne($db);
        $this->traitementModel = new Traitement($db);
        $this->rattacherModel = new Rattacher($db);
        $this->utilisateurModel = $utilisateurModel ?? new Utilisateur($db);
        $this->supervisionService = $supervisionService;
    }

    // --- GESTION DES GROUPES UTILISATEURS ---

    /**
     * Crée un nouveau groupe d'utilisateurs.
     * @param string $idGroupeUtilisateur L'ID unique du groupe (ex: 'GRP_COMMISSION').
     * @param string $libelleGroupeUtilisateur Le libellé du groupe.
     * @return bool Vrai si le groupe a été créé.
     * @throws DoublonException Si l'ID du groupe existe déjà.
     * @throws OperationImpossibleException En cas d'échec de création.
     */
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
        } catch (DoublonException $e) {
            $this->groupeUtilisateurModel->annulerTransaction();
            throw $e;
        } catch (\Exception $e) {
            $this->groupeUtilisateurModel->annulerTransaction();
            $this->supervisionService->enregistrerAction(
                $_SESSION['user_id'] ?? 'SYSTEM',
                'ECHEC_CREATION_GROUPE_UTILISATEUR',
                "Erreur création groupe utilisateur '{$idGroupeUtilisateur}': " . $e->getMessage()
            );
            throw $e;
        }
    }

    /**
     * Modifie un groupe d'utilisateurs existant.
     * @param string $idGroupeUtilisateur L'ID du groupe à modifier.
     * @param array $donnees Les données à mettre à jour (ex: 'libelle_groupe_utilisateur').
     * @return bool Vrai si la mise à jour a réussi.
     * @throws ElementNonTrouveException Si le groupe n'est pas trouvé.
     * @throws DoublonException Si le nouveau libellé existe déjà.
     */
    public function modifierGroupeUtilisateur(string $idGroupeUtilisateur, array $donnees): bool
    {
        $groupe = $this->groupeUtilisateurModel->trouverParIdentifiant($idGroupeUtilisateur);
        if (!$groupe) {
            throw new ElementNonTrouveException("Groupe utilisateur '{$idGroupeUtilisateur}' non trouvé.");
        }

        $this->groupeUtilisateurModel->commencerTransaction();
        try {
            $success = $this->groupeUtilisateurModel->mettreAJourParIdentifiant($idGroupeUtilisateur, $donnees);
            if (!$success) {
                throw new OperationImpossibleException("Échec de la mise à jour du groupe utilisateur.");
            }
            $this->groupeUtilisateurModel->validerTransaction();
            $this->supervisionService->enregistrerAction(
                $_SESSION['user_id'] ?? 'SYSTEM',
                'MODIF_GROUPE_UTILISATEUR',
                "Groupe utilisateur '{$idGroupeUtilisateur}' mis à jour.",
                $idGroupeUtilisateur,
                'GroupeUtilisateur'
            );
            return true;
        } catch (DoublonException $e) {
            $this->groupeUtilisateurModel->annulerTransaction();
            throw $e;
        } catch (\Exception $e) {
            $this->groupeUtilisateurModel->annulerTransaction();
            $this->supervisionService->enregistrerAction(
                $_SESSION['user_id'] ?? 'SYSTEM',
                'ECHEC_MODIF_GROUPE_UTILISATEUR',
                "Erreur modification groupe utilisateur '{$idGroupeUtilisateur}': " . $e->getMessage()
            );
            throw $e;
        }
    }

    /**
     * Supprime un groupe d'utilisateurs.
     * @param string $idGroupeUtilisateur L'ID du groupe à supprimer.
     * @return bool Vrai si la suppression a réussi.
     * @throws ElementNonTrouveException Si le groupe n'est pas trouvé.
     * @throws OperationImpossibleException Si le groupe a des utilisateurs ou des permissions rattachées (gestion des cascades).
     */
    public function supprimerGroupeUtilisateur(string $idGroupeUtilisateur): bool
    {
        $groupe = $this->groupeUtilisateurModel->trouverParIdentifiant($idGroupeUtilisateur);
        if (!$groupe) {
            throw new ElementNonTrouveException("Groupe utilisateur '{$idGroupeUtilisateur}' non trouvé.");
        }

        // Vérifier s'il y a des utilisateurs rattachés à ce groupe
        if ($this->utilisateurModel->compterParCritere(['id_groupe_utilisateur' => $idGroupeUtilisateur]) > 0) {
            throw new OperationImpossibleException("Impossible de supprimer le groupe : des utilisateurs y sont encore rattachés.");
        }
        // Vérifier s'il y a des permissions rattachées à ce groupe
        if ($this->rattacherModel->compterParCritere(['id_groupe_utilisateur' => $idGroupeUtilisateur]) > 0) {
            throw new OperationImpossibleException("Impossible de supprimer le groupe : des permissions lui sont encore rattachées.");
        }

        $this->groupeUtilisateurModel->commencerTransaction();
        try {
            $success = $this->groupeUtilisateurModel->supprimerParIdentifiant($idGroupeUtilisateur);
            if (!$success) {
                throw new OperationImpossibleException("Échec de la suppression du groupe utilisateur.");
            }
            $this->groupeUtilisateurModel->validerTransaction();
            $this->supervisionService->enregistrerAction(
                $_SESSION['user_id'] ?? 'SYSTEM',
                'SUPPRESSION_GROUPE_UTILISATEUR',
                "Groupe utilisateur '{$idGroupeUtilisateur}' supprimé.",
                $idGroupeUtilisateur,
                'GroupeUtilisateur'
            );
            return true;
        } catch (\Exception $e) {
            $this->groupeUtilisateurModel->annulerTransaction();
            $this->supervisionService->enregistrerAction(
                $_SESSION['user_id'] ?? 'SYSTEM',
                'ECHEC_SUPPRESSION_GROUPE_UTILISATEUR',
                "Erreur suppression groupe utilisateur '{$idGroupeUtilisateur}': " . $e->getMessage()
            );
            throw $e;
        }
    }

    public function recupererGroupeUtilisateurParId(string $idGroupeUtilisateur): ?array
    {
        return $this->groupeUtilisateurModel->trouverParIdentifiant($idGroupeUtilisateur);
    }

    public function recupererGroupeUtilisateurParCode(string $codeGroupe): ?array
    {
        return $this->groupeUtilisateurModel->trouverUnParCritere(['id_groupe_utilisateur' => $codeGroupe]);
    }

    public function listerGroupesUtilisateur(): array
    {
        return $this->groupeUtilisateurModel->trouverTout();
    }

    // --- GESTION DES TYPES UTILISATEURS ---

    public function creerTypeUtilisateur(string $idTypeUtilisateur, string $libelleTypeUtilisateur): bool
    {
        $this->typeUtilisateurModel->commencerTransaction();
        try {
            $data = [
                'id_type_utilisateur' => $idTypeUtilisateur,
                'libelle_type_utilisateur' => $libelleTypeUtilisateur
            ];
            $success = $this->typeUtilisateurModel->creer($data);
            if (!$success) {
                throw new OperationImpossibleException("Échec de la création du type d'utilisateur.");
            }
            $this->typeUtilisateurModel->validerTransaction();
            $this->supervisionService->enregistrerAction(
                $_SESSION['user_id'] ?? 'SYSTEM',
                'CREATION_TYPE_UTILISATEUR',
                "Type d'utilisateur '{$idTypeUtilisateur}' créé.",
                $idTypeUtilisateur,
                'TypeUtilisateur'
            );
            return true;
        } catch (DoublonException $e) {
            $this->typeUtilisateurModel->annulerTransaction();
            throw $e;
        } catch (\Exception $e) {
            $this->typeUtilisateurModel->annulerTransaction();
            $this->supervisionService->enregistrerAction(
                $_SESSION['user_id'] ?? 'SYSTEM',
                'ECHEC_CREATION_TYPE_UTILISATEUR',
                "Erreur création type utilisateur '{$idTypeUtilisateur}': " . $e->getMessage()
            );
            throw $e;
        }
    }

    public function modifierTypeUtilisateur(string $idTypeUtilisateur, array $donnees): bool
    {
        $typeUser = $this->typeUtilisateurModel->trouverParIdentifiant($idTypeUtilisateur);
        if (!$typeUser) {
            throw new ElementNonTrouveException("Type d'utilisateur '{$idTypeUtilisateur}' non trouvé.");
        }

        $this->typeUtilisateurModel->commencerTransaction();
        try {
            $success = $this->typeUtilisateurModel->mettreAJourParIdentifiant($idTypeUtilisateur, $donnees);
            if (!$success) {
                throw new OperationImpossibleException("Échec de la mise à jour du type d'utilisateur.");
            }
            $this->typeUtilisateurModel->validerTransaction();
            $this->supervisionService->enregistrerAction(
                $_SESSION['user_id'] ?? 'SYSTEM',
                'MODIF_TYPE_UTILISATEUR',
                "Type d'utilisateur '{$idTypeUtilisateur}' mis à jour.",
                $idTypeUtilisateur,
                'TypeUtilisateur'
            );
            return true;
        } catch (DoublonException $e) {
            $this->typeUtilisateurModel->annulerTransaction();
            throw $e;
        } catch (\Exception $e) {
            $this->typeUtilisateurModel->annulerTransaction();
            $this->supervisionService->enregistrerAction(
                $_SESSION['user_id'] ?? 'SYSTEM',
                'ECHEC_MODIF_TYPE_UTILISATEUR',
                "Erreur modification type utilisateur '{$idTypeUtilisateur}': " . $e->getMessage()
            );
            throw $e;
        }
    }

    public function supprimerTypeUtilisateur(string $idTypeUtilisateur): bool
    {
        $typeUser = $this->typeUtilisateurModel->trouverParIdentifiant($idTypeUtilisateur);
        if (!$typeUser) {
            throw new ElementNonTrouveException("Type d'utilisateur '{$idTypeUtilisateur}' non trouvé.");
        }
        if ($this->utilisateurModel->compterParCritere(['id_type_utilisateur' => $idTypeUtilisateur]) > 0) {
            throw new OperationImpossibleException("Impossible de supprimer le type d'utilisateur : des utilisateurs y sont encore rattachés.");
        }

        $this->typeUtilisateurModel->commencerTransaction();
        try {
            $success = $this->typeUtilisateurModel->supprimerParIdentifiant($idTypeUtilisateur);
            if (!$success) {
                throw new OperationImpossibleException("Échec de la suppression du type d'utilisateur.");
            }
            $this->typeUtilisateurModel->validerTransaction();
            $this->supervisionService->enregistrerAction(
                $_SESSION['user_id'] ?? 'SYSTEM',
                'SUPPRESSION_TYPE_UTILISATEUR',
                "Type d'utilisateur '{$idTypeUtilisateur}' supprimé.",
                $idTypeUtilisateur,
                'TypeUtilisateur'
            );
            return true;
        } catch (\Exception $e) {
            $this->typeUtilisateurModel->annulerTransaction();
            $this->supervisionService->enregistrerAction(
                $_SESSION['user_id'] ?? 'SYSTEM',
                'ECHEC_SUPPRESSION_TYPE_UTILISATEUR',
                "Erreur suppression type utilisateur '{$idTypeUtilisateur}': " . $e->getMessage()
            );
            throw $e;
        }
    }

    public function recupererTypeUtilisateurParId(string $idTypeUtilisateur): ?array
    {
        return $this->typeUtilisateurModel->trouverParIdentifiant($idTypeUtilisateur);
    }

    public function recupererTypeUtilisateurParCode(string $codeType): ?array
    {
        return $this->typeUtilisateurModel->trouverUnParCritere(['id_type_utilisateur' => $codeType]);
    }

    public function listerTypesUtilisateur(): array
    {
        return $this->typeUtilisateurModel->trouverTout();
    }

    // --- GESTION DES NIVEAUX D'ACCÈS AUX DONNÉES ---
    // Similaire aux méthodes de gestion des groupes et types

    public function creerNiveauAcces(string $idNiveauAcces, string $libelleNiveauAcces): bool
    {
        $this->niveauAccesDonneModel->commencerTransaction();
        try {
            $data = [
                'id_niveau_acces_donne' => $idNiveauAcces,
                'libelle_niveau_acces_donne' => $libelleNiveauAcces
            ];
            $success = $this->niveauAccesDonneModel->creer($data);
            if (!$success) {
                throw new OperationImpossibleException("Échec de la création du niveau d'accès.");
            }
            $this->niveauAccesDonneModel->validerTransaction();
            $this->supervisionService->enregistrerAction(
                $_SESSION['user_id'] ?? 'SYSTEM',
                'CREATION_NIVEAU_ACCES',
                "Niveau d'accès '{$idNiveauAcces}' créé.",
                $idNiveauAcces,
                'NiveauAccesDonne'
            );
            return true;
        } catch (DoublonException $e) {
            $this->niveauAccesDonneModel->annulerTransaction();
            throw $e;
        } catch (\Exception $e) {
            $this->niveauAccesDonneModel->annulerTransaction();
            $this->supervisionService->enregistrerAction(
                $_SESSION['user_id'] ?? 'SYSTEM',
                'ECHEC_CREATION_NIVEAU_ACCES',
                "Erreur création niveau d'accès '{$idNiveauAcces}': " . $e->getMessage()
            );
            throw $e;
        }
    }

    public function modifierNiveauAcces(string $idNiveauAcces, array $donnees): bool
    {
        $niveauAcces = $this->niveauAccesDonneModel->trouverParIdentifiant($idNiveauAcces);
        if (!$niveauAcces) {
            throw new ElementNonTrouveException("Niveau d'accès '{$idNiveauAcces}' non trouvé.");
        }

        $this->niveauAccesDonneModel->commencerTransaction();
        try {
            $success = $this->niveauAccesDonneModel->mettreAJourParIdentifiant($idNiveauAcces, $donnees);
            if (!$success) {
                throw new OperationImpossibleException("Échec de la mise à jour du niveau d'accès.");
            }
            $this->niveauAccesDonneModel->validerTransaction();
            $this->supervisionService->enregistrerAction(
                $_SESSION['user_id'] ?? 'SYSTEM',
                'MODIF_NIVEAU_ACCES',
                "Niveau d'accès '{$idNiveauAcces}' mis à jour.",
                $idNiveauAcces,
                'NiveauAccesDonne'
            );
            return true;
        } catch (DoublonException $e) {
            $this->niveauAccesDonneModel->annulerTransaction();
            throw $e;
        } catch (\Exception $e) {
            $this->niveauAccesDonneModel->annulerTransaction();
            $this->supervisionService->enregistrerAction(
                $_SESSION['user_id'] ?? 'SYSTEM',
                'ECHEC_MODIF_NIVEAU_ACCES',
                "Erreur modification niveau d'accès '{$idNiveauAcces}': " . $e->getMessage()
            );
            throw $e;
        }
    }

    public function supprimerNiveauAcces(string $idNiveauAcces): bool
    {
        $niveauAcces = $this->niveauAccesDonneModel->trouverParIdentifiant($idNiveauAcces);
        if (!$niveauAcces) {
            throw new ElementNonTrouveException("Niveau d'accès '{$idNiveauAcces}' non trouvé.");
        }
        if ($this->utilisateurModel->compterParCritere(['id_niveau_acces_donne' => $idNiveauAcces]) > 0) {
            throw new OperationImpossibleException("Impossible de supprimer le niveau d'accès : des utilisateurs y sont encore rattachés.");
        }

        $this->niveauAccesDonneModel->commencerTransaction();
        try {
            $success = $this->niveauAccesDonneModel->supprimerParIdentifiant($idNiveauAcces);
            if (!$success) {
                throw new OperationImpossibleException("Échec de la suppression du niveau d'accès.");
            }
            $this->niveauAccesDonneModel->validerTransaction();
            $this->supervisionService->enregistrerAction(
                $_SESSION['user_id'] ?? 'SYSTEM',
                'SUPPRESSION_NIVEAU_ACCES',
                "Niveau d'accès '{$idNiveauAcces}' supprimé.",
                $idNiveauAcces,
                'NiveauAccesDonne'
            );
            return true;
        } catch (\Exception $e) {
            $this->niveauAccesDonneModel->annulerTransaction();
            $this->supervisionService->enregistrerAction(
                $_SESSION['user_id'] ?? 'SYSTEM',
                'ECHEC_SUPPRESSION_NIVEAU_ACCES',
                "Erreur suppression niveau d'accès '{$idNiveauAcces}': " . $e->getMessage()
            );
            throw $e;
        }
    }

    public function recupererNiveauAccesParId(string $idNiveauAcces): ?array
    {
        return $this->niveauAccesDonneModel->trouverParIdentifiant($idNiveauAcces);
    }

    public function recupererNiveauAccesParCode(string $codeNiveau): ?array
    {
        return $this->niveauAccesDonneModel->trouverUnParCritere(['id_niveau_acces_donne' => $codeNiveau]);
    }

    public function listerNiveauxAcces(): array
    {
        return $this->niveauAccesDonneModel->trouverTout();
    }

    // --- GESTION DES TRAITEMENTS (PERMISSIONS) ---
    // Similaire aux méthodes de gestion des groupes et types

    public function creerTraitement(string $idTraitement, string $libelleTraitement): bool
    {
        $this->traitementModel->commencerTransaction();
        try {
            $data = [
                'id_traitement' => $idTraitement,
                'libelle_traitement' => $libelleTraitement
            ];
            $success = $this->traitementModel->creer($data);
            if (!$success) {
                throw new OperationImpossibleException("Échec de la création du traitement.");
            }
            $this->traitementModel->validerTransaction();
            $this->supervisionService->enregistrerAction(
                $_SESSION['user_id'] ?? 'SYSTEM',
                'CREATION_TRAITEMENT',
                "Traitement '{$idTraitement}' créé.",
                $idTraitement,
                'Traitement'
            );
            return true;
        } catch (DoublonException $e) {
            $this->traitementModel->annulerTransaction();
            throw $e;
        } catch (\Exception $e) {
            $this->traitementModel->annulerTransaction();
            $this->supervisionService->enregistrerAction(
                $_SESSION['user_id'] ?? 'SYSTEM',
                'ECHEC_CREATION_TRAITEMENT',
                "Erreur création traitement '{$idTraitement}': " . $e->getMessage()
            );
            throw $e;
        }
    }

    public function modifierTraitement(string $idTraitement, array $donnees): bool
    {
        $traitement = $this->traitementModel->trouverParIdentifiant($idTraitement);
        if (!$traitement) {
            throw new ElementNonTrouveException("Traitement '{$idTraitement}' non trouvé.");
        }

        $this->traitementModel->commencerTransaction();
        try {
            $success = $this->traitementModel->mettreAJourParIdentifiant($idTraitement, $donnees);
            if (!$success) {
                throw new OperationImpossibleException("Échec de la mise à jour du traitement.");
            }
            $this->traitementModel->validerTransaction();
            $this->supervisionService->enregistrerAction(
                $_SESSION['user_id'] ?? 'SYSTEM',
                'MODIF_TRAITEMENT',
                "Traitement '{$idTraitement}' mis à jour.",
                $idTraitement,
                'Traitement'
            );
            return true;
        } catch (DoublonException $e) {
            $this->traitementModel->annulerTransaction();
            throw $e;
        } catch (\Exception $e) {
            $this->traitementModel->annulerTransaction();
            $this->supervisionService->enregistrerAction(
                $_SESSION['user_id'] ?? 'SYSTEM',
                'ECHEC_MODIF_TRAITEMENT',
                "Erreur modification traitement '{$idTraitement}': " . $e->getMessage()
            );
            throw $e;
        }
    }

    public function supprimerTraitement(string $idTraitement): bool
    {
        $traitement = $this->traitementModel->trouverParIdentifiant($idTraitement);
        if (!$traitement) {
            throw new ElementNonTrouveException("Traitement '{$idTraitement}' non trouvé.");
        }
        // Vérifier s'il y a des rattachements à ce traitement
        if ($this->rattacherModel->compterParCritere(['id_traitement' => $idTraitement]) > 0) {
            throw new OperationImpossibleException("Impossible de supprimer le traitement : des rattachements existent.");
        }

        $this->traitementModel->commencerTransaction();
        try {
            $success = $this->traitementModel->supprimerParIdentifiant($idTraitement);
            if (!$success) {
                throw new OperationImpossibleException("Échec de la suppression du traitement.");
            }
            $this->traitementModel->validerTransaction();
            $this->supervisionService->enregistrerAction(
                $_SESSION['user_id'] ?? 'SYSTEM',
                'SUPPRESSION_TRAITEMENT',
                "Traitement '{$idTraitement}' supprimé.",
                $idTraitement,
                'Traitement'
            );
            return true;
        } catch (\Exception $e) {
            $this->traitementModel->annulerTransaction();
            $this->supervisionService->enregistrerAction(
                $_SESSION['user_id'] ?? 'SYSTEM',
                'ECHEC_SUPPRESSION_TRAITEMENT',
                "Erreur suppression traitement '{$idTraitement}': " . $e->getMessage()
            );
            throw $e;
        }
    }

    public function recupererTraitementParId(string $idTraitement): ?array
    {
        return $this->traitementModel->trouverParIdentifiant($idTraitement);
    }

    public function recupererTraitementParCode(string $codeTraitement): ?array
    {
        return $this->traitementModel->trouverUnParCritere(['id_traitement' => $codeTraitement]);
    }

    public function listerTraitements(): array
    {
        return $this->traitementModel->trouverTout();
    }

    // --- GESTION DE L'ATTRIBUTION DES PERMISSIONS (RATTACHEMENT) ---

    /**
     * Attribue une permission (traitement) à un groupe d'utilisateurs.
     * @param string $idGroupeUtilisateur L'ID du groupe.
     * @param string $idTraitement L'ID du traitement (permission).
     * @return bool Vrai si l'attribution a réussi.
     * @throws ElementNonTrouveException Si groupe ou traitement n'est pas trouvé.
     * @throws DoublonException Si le rattachement existe déjà.
     */
    public function attribuerPermissionGroupe(string $idGroupeUtilisateur, string $idTraitement): bool
    {
        $this->rattacherModel->commencerTransaction();
        try {
            if (!$this->groupeUtilisateurModel->trouverParIdentifiant($idGroupeUtilisateur)) {
                throw new ElementNonTrouveException("Groupe utilisateur '{$idGroupeUtilisateur}' non trouvé.");
            }
            if (!$this->traitementModel->trouverParIdentifiant($idTraitement)) {
                throw new ElementNonTrouveException("Traitement '{$idTraitement}' non trouvé.");
            }

            $data = [
                'id_groupe_utilisateur' => $idGroupeUtilisateur,
                'id_traitement' => $idTraitement
            ];
            $success = $this->rattacherModel->creer($data);
            if (!$success) {
                throw new OperationImpossibleException("Échec de l'attribution de la permission au groupe.");
            }
            $this->rattacherModel->validerTransaction();
            $this->supervisionService->enregistrerAction(
                $_SESSION['user_id'] ?? 'SYSTEM',
                'ATTRIB_PERM_GROUPE',
                "Permission '{$idTraitement}' attribuée au groupe '{$idGroupeUtilisateur}'."
            );
            return true;
        } catch (DoublonException $e) {
            $this->rattacherModel->annulerTransaction();
            throw $e;
        } catch (\Exception $e) {
            $this->rattacherModel->annulerTransaction();
            $this->supervisionService->enregistrerAction(
                $_SESSION['user_id'] ?? 'SYSTEM',
                'ECHEC_ATTRIB_PERM_GROUPE',
                "Erreur attribution permission à groupe '{$idGroupeUtilisateur}': " . $e->getMessage()
            );
            throw $e;
        }
    }

    /**
     * Retire une permission (traitement) d'un groupe d'utilisateurs.
     * @param string $idGroupeUtilisateur L'ID du groupe.
     * @param string $idTraitement L'ID du traitement (permission).
     * @return bool Vrai si le retrait a réussi.
     * @throws ElementNonTrouveException Si le rattachement n'est pas trouvé.
     */
    public function retirerPermissionGroupe(string $idGroupeUtilisateur, string $idTraitement): bool
    {
        $this->rattacherModel->commencerTransaction();
        try {
            $success = $this->rattacherModel->supprimerParClesInternes([
                'id_groupe_utilisateur' => $idGroupeUtilisateur,
                'id_traitement' => $idTraitement
            ]);
            if (!$success) {
                throw new OperationImpossibleException("Échec du retrait de la permission du groupe.");
            }
            $this->rattacherModel->validerTransaction();
            $this->supervisionService->enregistrerAction(
                $_SESSION['user_id'] ?? 'SYSTEM',
                'RETRAIT_PERM_GROUPE',
                "Permission '{$idTraitement}' retirée du groupe '{$idGroupeUtilisateur}'."
            );
            return true;
        } catch (\Exception $e) {
            $this->rattacherModel->annulerTransaction();
            $this->supervisionService->enregistrerAction(
                $_SESSION['user_id'] ?? 'SYSTEM',
                'ECHEC_RETRAIT_PERM_GROUPE',
                "Erreur retrait permission de groupe '{$idGroupeUtilisateur}': " . $e->getMessage()
            );
            throw $e;
        }
    }

    /**
     * Récupère la liste des permissions (traitements) pour un groupe donné.
     * @param string $idGroupeUtilisateur L'ID du groupe utilisateur.
     * @return array La liste des IDs de traitements.
     * @throws ElementNonTrouveException Si le groupe n'est pas trouvé.
     */
    public function recupererPermissionsPourGroupe(string $idGroupeUtilisateur): array
    {
        if (!$this->groupeUtilisateurModel->trouverParIdentifiant($idGroupeUtilisateur)) {
            throw new ElementNonTrouveException("Groupe utilisateur '{$idGroupeUtilisateur}' non trouvé.");
        }
        $rattachements = $this->rattacherModel->trouverParCritere(['id_groupe_utilisateur' => $idGroupeUtilisateur], ['id_traitement']);
        return array_column($rattachements, 'id_traitement');
    }

    /**
     * Récupère la liste des groupes auxquels une permission est attribuée.
     * @param string $idTraitement L'ID du traitement (permission).
     * @return array La liste des IDs de groupes utilisateurs.
     * @throws ElementNonTrouveException Si le traitement n'est pas trouvé.
     */
    public function recupererGroupesPourPermission(string $idTraitement): array
    {
        if (!$this->traitementModel->trouverParIdentifiant($idTraitement)) {
            throw new ElementNonTrouveException("Traitement '{$idTraitement}' non trouvé.");
        }
        $rattachements = $this->rattacherModel->trouverParCritere(['id_traitement' => $idTraitement], ['id_groupe_utilisateur']);
        return array_column($rattachements, 'id_groupe_utilisateur');
    }

    /**
     * Vérifie si l'utilisateur connecté (dans la session) possède une permission spécifique.
     * Cette méthode se base sur les permissions chargées en session lors de la connexion.
     * @param string $permissionCode Le code de la permission à vérifier.
     * @return bool Vrai si l'utilisateur possède la permission, faux sinon.
     */
    public function utilisateurPossedePermission(string $permissionCode): bool
    {
        // Les permissions sont supposées être chargées dans $_SESSION['user_permissions']
        return isset($_SESSION['user_permissions']) && in_array($permissionCode, $_SESSION['user_permissions']);
    }

    /**
     * Vérifie si un groupe possède une permission spécifique.
     * @param string $idGroupeUtilisateur L'ID du groupe.
     * @param string $permissionCode Le code de la permission à vérifier.
     * @return bool Vrai si le groupe possède la permission, faux sinon.
     * @throws ElementNonTrouveException Si le groupe n'est pas trouvé.
     */
    public function groupePossedePermission(string $idGroupeUtilisateur, string $permissionCode): bool
    {
        if (!$this->groupeUtilisateurModel->trouverParIdentifiant($idGroupeUtilisateur)) {
            throw new ElementNonTrouveException("Groupe utilisateur '{$idGroupeUtilisateur}' non trouvé.");
        }
        return $this->rattacherModel->trouverRattachementParCles($idGroupeUtilisateur, $permissionCode) !== null;
    }
}