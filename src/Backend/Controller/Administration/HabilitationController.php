<?php
namespace App\Backend\Controller\Administration;

use App\Backend\Controller\BaseController;
use App\Backend\Service\Authentication\ServiceAuthentication;
use App\Backend\Service\Permissions\ServicePermissions; // Importer le service
use App\Backend\Util\FormValidator;
use App\Backend\Exception\ElementNonTrouveException;
use App\Backend\Exception\OperationImpossibleException;
use App\Backend\Exception\DoublonException;
use App\Backend\Exception\ValidationException;

class HabilitationController extends BaseController
{
    protected ServicePermissions $permissionService;

    public function __construct(
        ServiceAuthentication $authService,
        ServicePermissions    $permissionService,
        FormValidator         $validator
    ) {
        parent::__construct($authService, $permissionService, $validator);
        $this->permissionService = $permissionService;
    }

    /**
     * Affiche la page principale de gestion des habilitations.
     * Peut lister les groupes, types d'utilisateurs, niveaux d'accès et traitements.
     */
    public function index(): void
    {
        $this->requirePermission('TRAIT_ADMIN_HABILITATIONS_ACCEDER'); // Permission générale

        try {
            $groupes = $this->permissionService->listerGroupesUtilisateur();
            $typesUtilisateur = $this->permissionService->listerTypesUtilisateur();
            $niveauxAcces = $this->permissionService->listerNiveauxAcces();
            $traitements = $this->permissionService->listerTraitements();

            $data = [
                'page_title' => 'Gestion des Habilitations',
                'groupes' => $groupes,
                'types_utilisateur' => $typesUtilisateur,
                'niveaux_acces' => $niveauxAcces,
                'traitements' => $traitements
            ];
            $this->render('Administration/Habilitations/index', $data); // Créer une vue index.php ou utiliser liste_groupes etc.
        } catch (\Exception $e) {
            $this->setFlashMessage('error', "Erreur lors du chargement des habilitations: " . $e->getMessage());
            $this->redirect('/dashboard/admin');
        }
    }

    // --- GESTION DES GROUPES D'UTILISATEURS (CRUD) ---

    /**
     * Affiche la liste des groupes d'utilisateurs.
     */
    public function listGroupes(): void
    {
        $this->requirePermission('TRAIT_ADMIN_HABILITATIONS_GROUPE_LISTER');
        try {
            $groupes = $this->permissionService->listerGroupesUtilisateur();
            $data = [
                'page_title' => 'Groupes d\'Utilisateurs',
                'groupes' => $groupes
            ];
            $this->render('Administration/Habilitations/liste_groupes', $data);
        } catch (\Exception $e) {
            $this->setFlashMessage('error', "Erreur chargement groupes: " . $e->getMessage());
            $this->redirect('/dashboard/admin/habilitations');
        }
    }

    /**
     * Affiche le formulaire de création de groupe ou la traite.
     */
    public function createGroupe(): void
    {
        $this->requirePermission('TRAIT_ADMIN_HABILITATIONS_GROUPE_CREER');
        if ($this->isPostRequest()) {
            $this->handleCreateGroupe();
        } else {
            $data = [
                'page_title' => 'Créer un Groupe',
                'form_action' => '/dashboard/admin/habilitations/groupes/create'
            ];
            $this->render('Administration/Habilitations/form_groupe', $data);
        }
    }

    /**
     * Traite la soumission du formulaire de création de groupe.
     */
    private function handleCreateGroupe(): void
    {
        $idGroupe = $this->getRequestData('id_groupe_utilisateur');
        $libelleGroupe = $this->getRequestData('libelle_groupe_utilisateur');

        $rules = [
            'id_groupe_utilisateur' => 'required|string|max:50',
            'libelle_groupe_utilisateur' => 'required|string|max:100'
        ];
        $this->validator->validate(['id_groupe_utilisateur' => $idGroupe, 'libelle_groupe_utilisateur' => $libelleGroupe], $rules);

        if (!$this->validator->isValid()) {
            $this->setFlashMessage('error', implode('<br>', $this->validator->getErrors()));
            $this->redirect('/dashboard/admin/habilitations/groupes/create');
        }

        try {
            $this->permissionService->creerGroupeUtilisateur($idGroupe, $libelleGroupe);
            $this->setFlashMessage('success', 'Groupe créé avec succès.');
            $this->redirect('/dashboard/admin/habilitations/groupes');
        } catch (DoublonException $e) {
            $this->setFlashMessage('error', 'Erreur: ' . $e->getMessage());
            $this->redirect('/dashboard/admin/habilitations/groupes/create');
        } catch (OperationImpossibleException $e) {
            $this->setFlashMessage('error', 'Opération impossible: ' . $e->getMessage());
            $this->redirect('/dashboard/admin/habilitations/groupes/create');
        } catch (\Exception $e) {
            $this->setFlashMessage('error', 'Une erreur inattendue est survenue: ' . $e->getMessage());
            $this->redirect('/dashboard/admin/habilitations/groupes/create');
        }
    }

    /**
     * Affiche le formulaire de modification de groupe ou la traite.
     * @param string $id L'ID du groupe à modifier.
     */
    public function editGroupe(string $id): void
    {
        $this->requirePermission('TRAIT_ADMIN_HABILITATIONS_GROUPE_MODIFIER');
        if ($this->isPostRequest()) {
            $this->handleEditGroupe($id);
        } else {
            try {
                $groupe = $this->permissionService->recupererGroupeUtilisateurParId($id);
                if (!$groupe) {
                    throw new ElementNonTrouveException("Groupe non trouvé.");
                }
                $data = [
                    'page_title' => 'Modifier Groupe',
                    'groupe' => $groupe,
                    'form_action' => "/dashboard/admin/habilitations/groupes/{$id}/edit"
                ];
                $this->render('Administration/Habilitations/form_groupe', $data);
            } catch (ElementNonTrouveException $e) {
                $this->setFlashMessage('error', $e->getMessage());
                $this->redirect('/dashboard/admin/habilitations/groupes');
            } catch (\Exception $e) {
                $this->setFlashMessage('error', 'Erreur: ' . $e->getMessage());
                $this->redirect('/dashboard/admin/habilitations/groupes');
            }
        }
    }

    /**
     * Traite la soumission du formulaire de modification de groupe.
     * @param string $id L'ID du groupe.
     */
    private function handleEditGroupe(string $id): void
    {
        $libelleGroupe = $this->getRequestData('libelle_groupe_utilisateur');
        $rules = ['libelle_groupe_utilisateur' => 'required|string|max:100'];
        $this->validator->validate(['libelle_groupe_utilisateur' => $libelleGroupe], $rules);

        if (!$this->validator->isValid()) {
            $this->setFlashMessage('error', implode('<br>', $this->validator->getErrors()));
            $this->redirect("/dashboard/admin/habilitations/groupes/{$id}/edit");
        }

        try {
            $this->permissionService->modifierGroupeUtilisateur($id, ['libelle_groupe_utilisateur' => $libelleGroupe]);
            $this->setFlashMessage('success', 'Groupe modifié avec succès.');
            $this->redirect('/dashboard/admin/habilitations/groupes');
        } catch (ElementNonTrouveException $e) {
            $this->setFlashMessage('error', $e->getMessage());
            $this->redirect('/dashboard/admin/habilitations/groupes');
        } catch (DoublonException $e) {
            $this->setFlashMessage('error', 'Erreur: ' . $e->getMessage());
            $this->redirect("/dashboard/admin/habilitations/groupes/{$id}/edit");
        } catch (OperationImpossibleException $e) {
            $this->setFlashMessage('error', 'Opération impossible: ' . $e->getMessage());
            $this->redirect("/dashboard/admin/habilitations/groupes/{$id}/edit");
        } catch (\Exception $e) {
            $this->setFlashMessage('error', 'Une erreur inattendue est survenue: ' . $e->getMessage());
            $this->redirect("/dashboard/admin/habilitations/groupes/{$id}/edit");
        }
    }

    /**
     * Supprime un groupe d'utilisateurs.
     * @param string $id L'ID du groupe à supprimer.
     */
    public function deleteGroupe(string $id): void
    {
        $this->requirePermission('TRAIT_ADMIN_HABILITATIONS_GROUPE_SUPPRIMER');
        try {
            $this->permissionService->supprimerGroupeUtilisateur($id);
            $this->setFlashMessage('success', 'Groupe supprimé avec succès.');
        } catch (ElementNonTrouveException $e) {
            $this->setFlashMessage('error', $e->getMessage());
        } catch (OperationImpossibleException $e) {
            $this->setFlashMessage('error', 'Impossible de supprimer ce groupe : ' . $e->getMessage());
        } catch (\Exception $e) {
            $this->setFlashMessage('error', 'Une erreur inattendue est survenue: ' . $e->getMessage());
        }
        $this->redirect('/dashboard/admin/habilitations/groupes');
    }

    // --- GESTION DES TYPES UTILISATEURS (CRUD) ---

    /**
     * Affiche la liste des types d'utilisateurs.
     */
    public function listTypesUtilisateur(): void
    {
        $this->requirePermission('TRAIT_ADMIN_HABILITATIONS_TYPE_UTILISATEUR_LISTER');
        try {
            $typesUtilisateur = $this->permissionService->listerTypesUtilisateur();
            $data = [
                'page_title' => 'Types d\'Utilisateurs',
                'types_utilisateur' => $typesUtilisateur
            ];
            $this->render('Administration/Habilitations/liste_types_utilisateur', $data);
        } catch (\Exception $e) {
            $this->setFlashMessage('error', "Erreur chargement types d'utilisateurs: " . $e->getMessage());
            $this->redirect('/dashboard/admin/habilitations');
        }
    }

    /**
     * Affiche le formulaire de création de type d'utilisateur ou la traite.
     */
    public function createTypeUtilisateur(): void
    {
        $this->requirePermission('TRAIT_ADMIN_HABILITATIONS_TYPE_UTILISATEUR_CREER');
        if ($this->isPostRequest()) {
            $this->handleCreateTypeUtilisateur();
        } else {
            $data = [
                'page_title' => 'Créer un Type d\'Utilisateur',
                'form_action' => '/dashboard/admin/habilitations/types-utilisateur/create'
            ];
            $this->render('Administration/Habilitations/form_type_utilisateur', $data);
        }
    }

    /**
     * Traite la soumission du formulaire de création de type d'utilisateur.
     */
    private function handleCreateTypeUtilisateur(): void
    {
        $idType = $this->getRequestData('id_type_utilisateur');
        $libelleType = $this->getRequestData('libelle_type_utilisateur');

        $rules = [
            'id_type_utilisateur' => 'required|string|max:50',
            'libelle_type_utilisateur' => 'required|string|max:100'
        ];
        $this->validator->validate(['id_type_utilisateur' => $idType, 'libelle_type_utilisateur' => $libelleType], $rules);

        if (!$this->validator->isValid()) {
            $this->setFlashMessage('error', implode('<br>', $this->validator->getErrors()));
            $this->redirect('/dashboard/admin/habilitations/types-utilisateur/create');
        }

        try {
            $this->permissionService->creerTypeUtilisateur($idType, $libelleType);
            $this->setFlashMessage('success', 'Type d\'utilisateur créé avec succès.');
            $this->redirect('/dashboard/admin/habilitations/types-utilisateur');
        } catch (DoublonException $e) {
            $this->setFlashMessage('error', 'Erreur: ' . $e->getMessage());
            $this->redirect('/dashboard/admin/habilitations/types-utilisateur/create');
        } catch (OperationImpossibleException $e) {
            $this->setFlashMessage('error', 'Opération impossible: ' . $e->getMessage());
            $this->redirect('/dashboard/admin/habilitations/types-utilisateur/create');
        } catch (\Exception $e) {
            $this->setFlashMessage('error', 'Une erreur inattendue est survenue: ' . $e->getMessage());
            $this->redirect('/dashboard/admin/habilitations/types-utilisateur/create');
        }
    }

    /**
     * Affiche le formulaire de modification de type d'utilisateur ou la traite.
     * @param string $id L'ID du type d'utilisateur à modifier.
     */
    public function editTypeUtilisateur(string $id): void
    {
        $this->requirePermission('TRAIT_ADMIN_HABILITATIONS_TYPE_UTILISATEUR_MODIFIER');
        if ($this->isPostRequest()) {
            $this->handleEditTypeUtilisateur($id);
        } else {
            try {
                $typeUser = $this->permissionService->recupererTypeUtilisateurParId($id);
                if (!$typeUser) {
                    throw new ElementNonTrouveException("Type d'utilisateur non trouvé.");
                }
                $data = [
                    'page_title' => 'Modifier Type d\'Utilisateur',
                    'type_utilisateur' => $typeUser,
                    'form_action' => "/dashboard/admin/habilitations/types-utilisateur/{$id}/edit"
                ];
                $this->render('Administration/Habilitations/form_type_utilisateur', $data);
            } catch (ElementNonTrouveException $e) {
                $this->setFlashMessage('error', $e->getMessage());
                $this->redirect('/dashboard/admin/habilitations/types-utilisateur');
            } catch (\Exception $e) {
                $this->setFlashMessage('error', 'Erreur: ' . $e->getMessage());
                $this->redirect('/dashboard/admin/habilitations/types-utilisateur');
            }
        }
    }

    /**
     * Traite la soumission du formulaire de modification de type d'utilisateur.
     * @param string $id L'ID du type d'utilisateur.
     */
    private function handleEditTypeUtilisateur(string $id): void
    {
        $libelleType = $this->getRequestData('libelle_type_utilisateur');
        $rules = ['libelle_type_utilisateur' => 'required|string|max:100'];
        $this->validator->validate(['libelle_type_utilisateur' => $libelleType], $rules);

        if (!$this->validator->isValid()) {
            $this->setFlashMessage('error', implode('<br>', $this->validator->getErrors()));
            $this->redirect("/dashboard/admin/habilitations/types-utilisateur/{$id}/edit");
        }

        try {
            $this->permissionService->modifierTypeUtilisateur($id, ['libelle_type_utilisateur' => $libelleType]);
            $this->setFlashMessage('success', 'Type d\'utilisateur modifié avec succès.');
            $this->redirect('/dashboard/admin/habilitations/types-utilisateur');
        } catch (ElementNonTrouveException $e) {
            $this->setFlashMessage('error', $e->getMessage());
            $this->redirect('/dashboard/admin/habilitations/types-utilisateur');
        } catch (DoublonException $e) {
            $this->setFlashMessage('error', 'Erreur: ' . $e->getMessage());
            $this->redirect("/dashboard/admin/habilitations/types-utilisateur/{$id}/edit");
        } catch (OperationImpossibleException $e) {
            $this->setFlashMessage('error', 'Opération impossible: ' . $e->getMessage());
            $this->redirect("/dashboard/admin/habilitations/types-utilisateur/{$id}/edit");
        } catch (\Exception $e) {
            $this->setFlashMessage('error', 'Une erreur inattendue est survenue: ' . $e->getMessage());
            $this->redirect("/dashboard/admin/habilitations/types-utilisateur/{$id}/edit");
        }
    }

    /**
     * Supprime un type d'utilisateur.
     * @param string $id L'ID du type d'utilisateur à supprimer.
     */
    public function deleteTypeUtilisateur(string $id): void
    {
        $this->requirePermission('TRAIT_ADMIN_HABILITATIONS_TYPE_UTILISATEUR_SUPPRIMER');
        try {
            $this->permissionService->supprimerTypeUtilisateur($id);
            $this->setFlashMessage('success', 'Type d\'utilisateur supprimé avec succès.');
        } catch (ElementNonTrouveException $e) {
            $this->setFlashMessage('error', $e->getMessage());
        } catch (OperationImpossibleException $e) {
            $this->setFlashMessage('error', 'Impossible de supprimer ce type d\'utilisateur : ' . $e->getMessage());
        } catch (\Exception $e) {
            $this->setFlashMessage('error', 'Une erreur inattendue est survenue: ' . $e->getMessage());
        }
        $this->redirect('/dashboard/admin/habilitations/types-utilisateur');
    }

    // --- GESTION DES NIVEAUX D'ACCÈS AUX DONNÉES (CRUD) ---

    /**
     * Affiche la liste des niveaux d'accès aux données.
     */
    public function listNiveauxAcces(): void
    {
        $this->requirePermission('TRAIT_ADMIN_HABILITATIONS_NIVEAU_ACCES_LISTER');
        try {
            $niveaux = $this->permissionService->listerNiveauxAcces();
            $data = [
                'page_title' => 'Niveaux d\'Accès aux Données',
                'niveaux_acces' => $niveaux
            ];
            $this->render('Administration/Habilitations/liste_niveaux_acces', $data);
        } catch (\Exception $e) {
            $this->setFlashMessage('error', "Erreur chargement niveaux d'accès: " . $e->getMessage());
            $this->redirect('/dashboard/admin/habilitations');
        }
    }

    /**
     * Affiche le formulaire de création de niveau d'accès ou la traite.
     */
    public function createNiveauAcces(): void
    {
        $this->requirePermission('TRAIT_ADMIN_HABILITATIONS_NIVEAU_ACCES_CREER');
        if ($this->isPostRequest()) {
            $this->handleCreateNiveauAcces();
        } else {
            $data = [
                'page_title' => 'Créer un Niveau d\'Accès',
                'form_action' => '/dashboard/admin/habilitations/niveaux-acces/create'
            ];
            $this->render('Administration/Habilitations/form_niveau_acces', $data);
        }
    }

    /**
     * Traite la soumission du formulaire de création de niveau d'accès.
     */
    private function handleCreateNiveauAcces(): void
    {
        $idNiveau = $this->getRequestData('id_niveau_acces_donne');
        $libelleNiveau = $this->getRequestData('libelle_niveau_acces_donne');

        $rules = [
            'id_niveau_acces_donne' => 'required|string|max:50',
            'libelle_niveau_acces_donne' => 'required|string|max:100'
        ];
        $this->validator->validate(['id_niveau_acces_donne' => $idNiveau, 'libelle_niveau_acces_donne' => $libelleNiveau], $rules);

        if (!$this->validator->isValid()) {
            $this->setFlashMessage('error', implode('<br>', $this->validator->getErrors()));
            $this->redirect('/dashboard/admin/habilitations/niveaux-acces/create');
        }

        try {
            $this->permissionService->creerNiveauAcces($idNiveau, $libelleNiveau);
            $this->setFlashMessage('success', 'Niveau d\'accès créé avec succès.');
            $this->redirect('/dashboard/admin/habilitations/niveaux-acces');
        } catch (DoublonException $e) {
            $this->setFlashMessage('error', 'Erreur: ' . $e->getMessage());
            $this->redirect('/dashboard/admin/habilitations/niveaux-acces/create');
        } catch (OperationImpossibleException $e) {
            $this->setFlashMessage('error', 'Opération impossible: ' . $e->getMessage());
            $this->redirect('/dashboard/admin/habilitations/niveaux-acces/create');
        } catch (\Exception $e) {
            $this->setFlashMessage('error', 'Une erreur inattendue est survenue: ' . $e->getMessage());
            $this->redirect('/dashboard/admin/habilitations/niveaux-acces/create');
        }
    }

    /**
     * Affiche le formulaire de modification de niveau d'accès ou la traite.
     * @param string $id L'ID du niveau d'accès à modifier.
     */
    public function editNiveauAcces(string $id): void
    {
        $this->requirePermission('TRAIT_ADMIN_HABILITATIONS_NIVEAU_ACCES_MODIFIER');
        if ($this->isPostRequest()) {
            $this->handleEditNiveauAcces($id);
        } else {
            try {
                $niveau = $this->permissionService->recupererNiveauAccesParId($id);
                if (!$niveau) {
                    throw new ElementNonTrouveException("Niveau d'accès non trouvé.");
                }
                $data = [
                    'page_title' => 'Modifier Niveau d\'Accès',
                    'niveau_acces' => $niveau,
                    'form_action' => "/dashboard/admin/habilitations/niveaux-acces/{$id}/edit"
                ];
                $this->render('Administration/Habilitations/form_niveau_acces', $data);
            } catch (ElementNonTrouveException $e) {
                $this->setFlashMessage('error', $e->getMessage());
                $this->redirect('/dashboard/admin/habilitations/niveaux-acces');
            } catch (\Exception $e) {
                $this->setFlashMessage('error', 'Erreur: ' . $e->getMessage());
                $this->redirect('/dashboard/admin/habilitations/niveaux-acces');
            }
        }
    }

    /**
     * Traite la soumission du formulaire de modification de niveau d'accès.
     * @param string $id L'ID du niveau d'accès.
     */
    private function handleEditNiveauAcces(string $id): void
    {
        $libelleNiveau = $this->getRequestData('libelle_niveau_acces_donne');
        $rules = ['libelle_niveau_acces_donne' => 'required|string|max:100'];
        $this->validator->validate(['libelle_niveau_acces_donne' => $libelleNiveau], $rules);

        if (!$this->validator->isValid()) {
            $this->setFlashMessage('error', implode('<br>', $this->validator->getErrors()));
            $this->redirect("/dashboard/admin/habilitations/niveaux-acces/{$id}/edit");
        }

        try {
            $this->permissionService->modifierNiveauAcces($id, ['libelle_niveau_acces_donne' => $libelleNiveau]);
            $this->setFlashMessage('success', 'Niveau d\'accès modifié avec succès.');
            $this->redirect('/dashboard/admin/habilitations/niveaux-acces');
        } catch (ElementNonTrouveException $e) {
            $this->setFlashMessage('error', $e->getMessage());
            $this->redirect('/dashboard/admin/habilitations/niveaux-acces');
        } catch (DoublonException $e) {
            $this->setFlashMessage('error', 'Erreur: ' . $e->getMessage());
            $this->redirect("/dashboard/admin/habilitations/niveaux-acces/{$id}/edit");
        } catch (OperationImpossibleException $e) {
            $this->setFlashMessage('error', 'Opération impossible: ' . $e->getMessage());
            $this->redirect("/dashboard/admin/habilitations/niveaux-acces/{$id}/edit");
        } catch (\Exception $e) {
            $this->setFlashMessage('error', 'Une erreur inattendue est survenue: ' . $e->getMessage());
            $this->redirect("/dashboard/admin/habilitations/niveaux-acces/{$id}/edit");
        }
    }

    /**
     * Supprime un niveau d'accès aux données.
     * @param string $id L'ID du niveau d'accès à supprimer.
     */
    public function deleteNiveauAcces(string $id): void
    {
        $this->requirePermission('TRAIT_ADMIN_HABILITATIONS_NIVEAU_ACCES_SUPPRIMER');
        try {
            $this->permissionService->supprimerNiveauAcces($id);
            $this->setFlashMessage('success', 'Niveau d\'accès supprimé avec succès.');
        } catch (ElementNonTrouveException $e) {
            $this->setFlashMessage('error', $e->getMessage());
        } catch (OperationImpossibleException $e) {
            $this->setFlashMessage('error', 'Impossible de supprimer ce niveau d\'accès : ' . $e->getMessage());
        } catch (\Exception $e) {
            $this->setFlashMessage('error', 'Une erreur inattendue est survenue: ' . $e->getMessage());
        }
        $this->redirect('/dashboard/admin/habilitations/niveaux-acces');
    }

    // --- GESTION DES TRAITEMENTS (PERMISSIONS) (CRUD) ---

    /**
     * Affiche la liste des traitements (permissions).
     */
    public function listTraitements(): void
    {
        $this->requirePermission('TRAIT_ADMIN_HABILITATIONS_TRAITEMENT_LISTER');
        try {
            $traitements = $this->permissionService->listerTraitements();
            $data = [
                'page_title' => 'Traitements (Permissions)',
                'traitements' => $traitements
            ];
            $this->render('Administration/Habilitations/liste_traitements', $data);
        } catch (\Exception $e) {
            $this->setFlashMessage('error', "Erreur chargement traitements: " . $e->getMessage());
            $this->redirect('/dashboard/admin/habilitations');
        }
    }

    /**
     * Affiche le formulaire de création de traitement ou la traite.
     */
    public function createTraitement(): void
    {
        $this->requirePermission('TRAIT_ADMIN_HABILITATIONS_TRAITEMENT_CREER');
        if ($this->isPostRequest()) {
            $this->handleCreateTraitement();
        } else {
            $data = [
                'page_title' => 'Créer un Traitement',
                'form_action' => '/dashboard/admin/habilitations/traitements/create'
            ];
            $this->render('Administration/Habilitations/form_traitement', $data);
        }
    }

    /**
     * Traite la soumission du formulaire de création de traitement.
     */
    private function handleCreateTraitement(): void
    {
        $idTraitement = $this->getRequestData('id_traitement');
        $libelleTraitement = $this->getRequestData('libelle_traitement');

        $rules = [
            'id_traitement' => 'required|string|max:50',
            'libelle_traitement' => 'required|string|max:100'
        ];
        $this->validator->validate(['id_traitement' => $idTraitement, 'libelle_traitement' => $libelleTraitement], $rules);

        if (!$this->validator->isValid()) {
            $this->setFlashMessage('error', implode('<br>', $this->validator->getErrors()));
            $this->redirect('/dashboard/admin/habilitations/traitements/create');
        }

        try {
            $this->permissionService->creerTraitement($idTraitement, $libelleTraitement);
            $this->setFlashMessage('success', 'Traitement créé avec succès.');
            $this->redirect('/dashboard/admin/habilitations/traitements');
        } catch (DoublonException $e) {
            $this->setFlashMessage('error', 'Erreur: ' . $e->getMessage());
            $this->redirect('/dashboard/admin/habilitations/traitements/create');
        } catch (OperationImpossibleException $e) {
            $this->setFlashMessage('error', 'Opération impossible: ' . $e->getMessage());
            $this->redirect('/dashboard/admin/habilitations/traitements/create');
        } catch (\Exception $e) {
            $this->setFlashMessage('error', 'Une erreur inattendue est survenue: ' . $e->getMessage());
            $this->redirect('/dashboard/admin/habilitations/traitements/create');
        }
    }

    /**
     * Affiche le formulaire de modification de traitement ou la traite.
     * @param string $id L'ID du traitement à modifier.
     */
    public function editTraitement(string $id): void
    {
        $this->requirePermission('TRAIT_ADMIN_HABILITATIONS_TRAITEMENT_MODIFIER');
        if ($this->isPostRequest()) {
            $this->handleEditTraitement($id);
        } else {
            try {
                $traitement = $this->permissionService->recupererTraitementParId($id);
                if (!$traitement) {
                    throw new ElementNonTrouveException("Traitement non trouvé.");
                }
                $data = [
                    'page_title' => 'Modifier Traitement',
                    'traitement' => $traitement,
                    'form_action' => "/dashboard/admin/habilitations/traitements/{$id}/edit"
                ];
                $this->render('Administration/Habilitations/form_traitement', $data);
            } catch (ElementNonTrouveException $e) {
                $this->setFlashMessage('error', $e->getMessage());
                $this->redirect('/dashboard/admin/habilitations/traitements');
            } catch (\Exception $e) {
                $this->setFlashMessage('error', 'Erreur: ' . $e->getMessage());
                $this->redirect('/dashboard/admin/habilitations/traitements');
            }
        }
    }

    /**
     * Traite la soumission du formulaire de modification de traitement.
     * @param string $id L'ID du traitement.
     */
    private function handleEditTraitement(string $id): void
    {
        $libelleTraitement = $this->getRequestData('libelle_traitement');
        $rules = ['libelle_traitement' => 'required|string|max:100'];
        $this->validator->validate(['libelle_traitement' => $libelleTraitement], $rules);

        if (!$this->validator->isValid()) {
            $this->setFlashMessage('error', implode('<br>', $this->validator->getErrors()));
            $this->redirect("/dashboard/admin/habilitations/traitements/{$id}/edit");
        }

        try {
            $this->permissionService->modifierTraitement($id, ['libelle_traitement' => $libelleTraitement]);
            $this->setFlashMessage('success', 'Traitement modifié avec succès.');
            $this->redirect('/dashboard/admin/habilitations/traitements');
        } catch (ElementNonTrouveException $e) {
            $this->setFlashMessage('error', $e->getMessage());
            $this->redirect('/dashboard/admin/habilitations/traitements');
        } catch (DoublonException $e) {
            $this->setFlashMessage('error', 'Erreur: ' . $e->getMessage());
            $this->redirect("/dashboard/admin/habilitations/traitements/{$id}/edit");
        } catch (OperationImpossibleException $e) {
            $this->setFlashMessage('error', 'Opération impossible: ' . $e->getMessage());
            $this->redirect("/dashboard/admin/habilitations/traitements/{$id}/edit");
        } catch (\Exception $e) {
            $this->setFlashMessage('error', 'Une erreur inattendue est survenue: ' . $e->getMessage());
            $this->redirect("/dashboard/admin/habilitations/traitements/{$id}/edit");
        }
    }

    /**
     * Supprime un traitement.
     * @param string $id L'ID du traitement à supprimer.
     */
    public function deleteTraitement(string $id): void
    {
        $this->requirePermission('TRAIT_ADMIN_HABILITATIONS_TRAITEMENT_SUPPRIMER');
        try {
            $this->permissionService->supprimerTraitement($id);
            $this->setFlashMessage('success', 'Traitement supprimé avec succès.');
        } catch (ElementNonTrouveException $e) {
            $this->setFlashMessage('error', $e->getMessage());
        } catch (OperationImpossibleException $e) {
            $this->setFlashMessage('error', 'Impossible de supprimer ce traitement : ' . $e->getMessage());
        } catch (\Exception $e) {
            $this->setFlashMessage('error', 'Une erreur inattendue est survenue: ' . $e->getMessage());
        }
        $this->redirect('/dashboard/admin/habilitations/traitements');
    }

    // --- GESTION DES RATTACHEMENTS (PERMISSIONS AUX GROUPES) ---

    /**
     * Affiche l'interface de gestion des rattachements pour un groupe donné.
     * @param string $idGroupe L'ID du groupe utilisateur.
     */
    public function manageRattachements(string $idGroupe): void
    {
        $this->requirePermission('TRAIT_ADMIN_HABILITATIONS_RATTACHEMENT_GERER');

        try {
            $groupe = $this->permissionService->recupererGroupeUtilisateurParId($idGroupe);
            if (!$groupe) {
                throw new ElementNonTrouveException("Groupe non trouvé.");
            }

            $allTraitements = $this->permissionService->listerTraitements();
            $permissionsDuGroupe = $this->permissionService->recupererPermissionsPourGroupe($idGroupe);

            $data = [
                'page_title' => "Gestion des Permissions pour {$groupe['libelle_groupe_utilisateur']}",
                'groupe' => $groupe,
                'all_traitements' => $allTraitements,
                'permissions_du_groupe' => $permissionsDuGroupe, // Tableau simple d'IDs
                'form_action' => "/dashboard/admin/habilitations/groupes/{$idGroupe}/rattachements/update"
            ];
            $this->render('Administration/Habilitations/gestion_rattachements', $data);
        } catch (ElementNonTrouveException $e) {
            $this->setFlashMessage('error', $e->getMessage());
            $this->redirect('/dashboard/admin/habilitations/groupes');
        } catch (\Exception $e) {
            $this->setFlashMessage('error', 'Erreur: ' . $e->getMessage());
            $this->redirect('/dashboard/admin/habilitations/groupes');
        }
    }

    /**
     * Traite la mise à jour des rattachements (permissions) pour un groupe.
     * @param string $idGroupe L'ID du groupe utilisateur.
     */
    public function updateRattachements(string $idGroupe): void
    {
        $this->requirePermission('TRAIT_ADMIN_HABILITATIONS_RATTACHEMENT_GERER');
        if (!$this->isPostRequest()) {
            $this->setFlashMessage('error', 'Méthode non autorisée.');
            $this->redirect("/dashboard/admin/habilitations/groupes/{$idGroupe}/rattachements");
        }

        $selectedTraitements = $this->getRequestData('traitements', []); // Tableau des IDs des traitements cochés

        try {
            $groupe = $this->permissionService->recupererGroupeUtilisateurParId($idGroupe);
            if (!$groupe) {
                throw new ElementNonTrouveException("Groupe non trouvé.");
            }

            $currentPermissions = $this->permissionService->recupererPermissionsPourGroupe($idGroupe);

            // Permissions à ajouter
            $toAdd = array_diff($selectedTraitements, $currentPermissions);
            foreach ($toAdd as $traitementId) {
                $this->permissionService->attribuerPermissionGroupe($idGroupe, $traitementId);
            }

            // Permissions à retirer
            $toRemove = array_diff($currentPermissions, $selectedTraitements);
            foreach ($toRemove as $traitementId) {
                $this->permissionService->retirerPermissionGroupe($idGroupe, $traitementId);
            }

            // IMPORTANT: Déclencher la synchronisation des sessions utilisateur après les changements RBAC
            // Vous aurez besoin d'une méthode dans ServiceAuthentication pour cela, déjà ajoutée :
            // $this->authService->synchroniserPermissionsSessionsUtilisateur($idUtilisateur);
            // Ou, si vous ne savez pas quels utilisateurs sont affectés par le changement de groupe,
            // vous pouvez déclencher une re-génération pour tous les utilisateurs de ce groupe.
            // Cela est géré dans ServicePermissions.php maintenant (via l'appel `enregistrerAction` qui peut déclencher la synchro).
            // Il faudrait une méthode explicite dans ServicePermissions pour synchroniser les sessions d'UN groupe.
            // Pour l'instant, on assume que la méthode de service attribuer/retirer gère la propagation si nécessaire.

            $this->setFlashMessage('success', 'Permissions mises à jour avec succès.');
            $this->redirect("/dashboard/admin/habilitations/groupes/{$idGroupe}/rattachements");

        } catch (ElementNonTrouveException $e) {
            $this->setFlashMessage('error', $e->getMessage());
            $this->redirect("/dashboard/admin/habilitations/groupes");
        } catch (OperationImpossibleException $e) {
            $this->setFlashMessage('error', 'Opération impossible: ' . $e->getMessage());
            $this->redirect("/dashboard/admin/habilitations/groupes/{$idGroupe}/rattachements");
        } catch (\Exception $e) {
            $this->setFlashMessage('error', 'Une erreur inattendue est survenue: ' . $e->getMessage());
            $this->redirect("/dashboard/admin/habilitations/groupes/{$idGroupe}/rattachements");
        }
    }
}