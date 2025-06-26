<?php
namespace App\Backend\Controller\PersonnelAdministratif;

use App\Backend\Controller\BaseController;
use App\Backend\Service\Authentication\ServiceAuthentication;
use App\Backend\Service\Permissions\ServicePermissions;
use App\Backend\Util\FormValidator;
use App\Backend\Service\GestionAcademique\ServiceGestionAcademique; // Importer le service
use App\Backend\Service\Reclamation\ServiceReclamation; // Importer le service
use App\Backend\Service\DocumentGenerator\ServiceDocumentGenerator; // Importer le service
use App\Backend\Service\ConfigurationSysteme\ServiceConfigurationSysteme; // Pour les référentiels
use App\Backend\Exception\ElementNonTrouveException;
use App\Backend\Exception\OperationImpossibleException;
use App\Backend\Exception\DoublonException;
use App\Backend\Exception\ValidationException;

class ScolariteController extends BaseController
{
    private ServiceGestionAcademique $gestionAcadService;
    private ServiceReclamation $reclamationService;
    private ServiceDocumentGenerator $documentGeneratorService;
    private ServiceConfigurationSysteme $configService;

    public function __construct(
        ServiceAuthentication       $authService,
        ServicePermissions          $permissionService,
        FormValidator               $validator,
        ServiceGestionAcademique    $gestionAcadService,
        ServiceReclamation          $reclamationService,
        ServiceDocumentGenerator    $documentGeneratorService,
        ServiceConfigurationSysteme $configService
    ) {
        parent::__construct($authService, $permissionService, $validator);
        $this->gestionAcadService = $gestionAcadService;
        $this->reclamationService = $reclamationService;
        $this->documentGeneratorService = $documentGeneratorService;
        $this->configService = $configService;
    }

    /**
     * Affiche la page principale de la scolarité.
     * Sert de menu vers les différentes sections de gestion.
     */
    public function index(): void
    {
        $this->requirePermission('TRAIT_PERS_ADMIN_SCOLARITE_ACCEDER'); // Permission générale

        $data = [
            'page_title' => 'Gestion de la Scolarité',
            'sections' => [
                ['label' => 'Gestion des Étudiants', 'url' => '/dashboard/personnel-admin/scolarite/etudiants', 'permission' => 'TRAIT_PERS_ADMIN_SCOLARITE_ETUDIANT_LISTER'],
                ['label' => 'Gestion des Inscriptions', 'url' => '/dashboard/personnel-admin/scolarite/inscriptions', 'permission' => 'TRAIT_PERS_ADMIN_SCOLARITE_INSCRIPTION_LISTER'],
                ['label' => 'Gestion des Notes', 'url' => '/dashboard/personnel-admin/scolarite/notes', 'permission' => 'TRAIT_PERS_ADMIN_SCOLARITE_NOTE_LISTER'],
                ['label' => 'Génération de Documents', 'url' => '/dashboard/personnel-admin/scolarite/documents', 'permission' => 'TRAIT_PERS_ADMIN_SCOLARITE_DOCUMENT_GENERER'],
                ['label' => 'Traitement des Réclamations', 'url' => '/dashboard/personnel-admin/scolarite/reclamations', 'permission' => 'TRAIT_PERS_ADMIN_SCOLARITE_RECLAMATION_TRAITER'],
                ['label' => 'Gestion des Pénalités', 'url' => '/dashboard/personnel-admin/scolarite/penalites', 'permission' => 'TRAIT_PERS_ADMIN_SCOLARITE_PENALITE_GERER'],
            ]
        ];
        // Filtrer les sections selon les permissions de l'utilisateur connecté
        $currentUserPermissions = $this->getCurrentUser()['user_permissions'] ?? [];
        $data['sections'] = array_filter($data['sections'], fn($section) => in_array($section['permission'], $currentUserPermissions));

        $this->render('PersonnelAdministratif/Scolarite/index', $data); // Créer cette vue
    }

    // --- GESTION DES ÉTUDIANTS (CRUD simplifié par RS, complet par Admin) ---

    /**
     * Affiche la liste des étudiants gérés par la scolarité.
     */
    public function listEtudiants(): void
    {
        $this->requirePermission('TRAIT_PERS_ADMIN_SCOLARITE_ETUDIANT_LISTER');

        try {
            $page = (int) $this->get('page', 1);
            $limit = 20;
            $criteres = ['id_type_utilisateur' => 'TYPE_ETUD']; // Seuls les étudiants

            $etudiants = $this->authService->listerUtilisateursAvecProfils($criteres, $page, $limit);
            // Vous pouvez ajouter des filtres supplémentaires (ex: par niveau d'étude, année académique)
            // $totalEtudiants = $this->authService->countUtilisateurs($criteres); // A créer

            $data = [
                'page_title' => 'Gestion des Étudiants',
                'etudiants' => $etudiants,
                'current_page' => $page,
                'items_per_page' => $limit,
                // 'total_items' => $totalEtudiants,
            ];
            $this->render('PersonnelAdministratif/Scolarite/gestion_etudiants_scolarite', $data);
        } catch (\Exception $e) {
            $this->setFlashMessage('error', "Erreur lors du chargement des étudiants: " . $e->getMessage());
            $this->redirect('/dashboard/personnel-admin/scolarite');
        }
    }

    /**
     * Affiche le formulaire pour valider un stage d'un étudiant ou le traite.
     * @param string $idEtudiant L'ID de l'étudiant.
     */
    public function validateStage(string $idEtudiant): void
    {
        $this->requirePermission('TRAIT_PERS_ADMIN_SCOLARITE_STAGE_VALIDER'); // Permission de valider stage

        if ($this->isPostRequest()) {
            $this->handleValidateStage($idEtudiant);
        } else {
            try {
                $etudiant = $this->authService->recupererUtilisateurCompletParNumero($idEtudiant);
                if (!$etudiant || $etudiant['id_type_utilisateur'] !== 'TYPE_ETUD') {
                    throw new ElementNonTrouveException("Étudiant non trouvé ou non valide.");
                }
                // Récupérer les informations de stage de l'étudiant
                // $stages = $this->gestionAcadService->listerStagesEtudiant($idEtudiant); // Méthode à créer

                $data = [
                    'page_title' => 'Valider Stage Étudiant',
                    'etudiant' => $etudiant,
                    // 'stages' => $stages,
                    'form_action' => "/dashboard/personnel-admin/scolarite/etudiants/{$idEtudiant}/validate-stage"
                ];
                $this->render('PersonnelAdministratif/Scolarite/validate_stage_form', $data); // Créer cette vue
            } catch (\Exception $e) {
                $this->setFlashMessage('error', 'Erreur chargement validation stage: ' . $e->getMessage());
                $this->redirect('/dashboard/personnel-admin/scolarite/etudiants');
            }
        }
    }

    /**
     * Traite la soumission du formulaire de validation de stage.
     * @param string $idEtudiant L'ID de l'étudiant.
     */
    private function handleValidateStage(string $idEtudiant): void
    {
        $currentUser = $this->getCurrentUser();
        if (!$currentUser || $currentUser['id_type_utilisateur'] !== 'TYPE_PERS_ADMIN') {
            $this->setFlashMessage('error', "Accès refusé.");
            $this->redirect("/dashboard/personnel-admin/scolarite/etudiants/{$idEtudiant}/validate-stage");
        }

        $idEntreprise = $this->post('id_entreprise');
        $dateDebutStage = $this->post('date_debut_stage');
        $dateFinStage = $this->post('date_fin_stage');
        $sujetStage = $this->post('sujet_stage');
        $nomTuteurEntreprise = $this->post('nom_tuteur_entreprise');

        $rules = [
            'id_entreprise' => 'required|string|max:50',
            'date_debut_stage' => 'required|date',
            'date_fin_stage' => 'nullable|date|after_or_equal:date_debut_stage',
            'sujet_stage' => 'nullable|string|max:500',
            'nom_tuteur_entreprise' => 'nullable|string|max:100',
        ];
        $validationData = [
            'id_entreprise' => $idEntreprise,
            'date_debut_stage' => $dateDebutStage,
            'date_fin_stage' => $dateFinStage,
            'sujet_stage' => $sujetStage,
            'nom_tuteur_entreprise' => $nomTuteurEntreprise,
        ];
        $this->validator->validate($validationData, $rules);

        if (!$this->validator->isValid()) {
            $this->setFlashMessage('error', implode('<br>', $this->validator->getErrors()));
            $this->redirect("/dashboard/personnel-admin/scolarite/etudiants/{$idEtudiant}/validate-stage");
        }

        try {
            $this->gestionAcadService->enregistrerInformationsStage(
                $idEtudiant,
                $idEntreprise,
                $dateDebutStage,
                $dateFinStage,
                $sujetStage,
                $nomTuteurEntreprise
            );
            $this->setFlashMessage('success', 'Informations de stage enregistrées/mises à jour avec succès.');
            $this->redirect('/dashboard/personnel-admin/scolarite/etudiants');
        } catch (ElementNonTrouveException $e) {
            $this->setFlashMessage('error', $e->getMessage());
            $this->redirect("/dashboard/personnel-admin/scolarite/etudiants/{$idEtudiant}/validate-stage");
        } catch (\Exception $e) {
            $this->setFlashMessage('error', 'Une erreur inattendue est survenue: ' . $e->getMessage());
            $this->redirect("/dashboard/personnel-admin/scolarite/etudiants/{$idEtudiant}/validate-stage");
        }
    }

    /**
     * Affiche la liste des pénalités d'un étudiant ou les gère.
     * @param string $idEtudiant L'ID de l'étudiant.
     */
    public function managePenalites(string $idEtudiant): void
    {
        $this->requirePermission('TRAIT_PERS_ADMIN_SCOLARITE_PENALITE_GERER');

        try {
            $etudiant = $this->authService->recupererUtilisateurCompletParNumero($idEtudiant);
            if (!$etudiant || $etudiant['id_type_utilisateur'] !== 'TYPE_ETUD') {
                throw new ElementNonTrouveException("Étudiant non trouvé ou non valide.");
            }

            $penalites = $this->gestionAcadService->listerPenalitesEtudiant($idEtudiant); // Nouvelle méthode au service gestionAcadService

            $data = [
                'page_title' => 'Gestion des Pénalités pour ' . ($etudiant['prenom'] ?? '') . ' ' . ($etudiant['nom'] ?? ''),
                'etudiant' => $etudiant,
                'penalites' => $penalites,
                'form_action_add' => "/dashboard/personnel-admin/scolarite/etudiants/{$idEtudiant}/penalites/add",
                'form_action_regularize' => "/dashboard/personnel-admin/scolarite/etudiants/{$idEtudiant}/penalites/regularize",
            ];
            $this->render('PersonnelAdministratif/Scolarite/manage_penalites', $data); // Créer cette vue
        } catch (\Exception $e) {
            $this->setFlashMessage('error', 'Erreur chargement pénalités: ' . $e->getMessage());
            $this->redirect('/dashboard/personnel-admin/scolarite/etudiants');
        }
    }

    /**
     * Traite l'ajout d'une nouvelle pénalité.
     * @param string $idEtudiant L'ID de l'étudiant.
     */
    public function addPenalite(string $idEtudiant): void
    {
        $this->requirePermission('TRAIT_PERS_ADMIN_SCOLARITE_PENALITE_APPLIQUER'); // Permission spécifique pour appliquer

        if (!$this->isPostRequest()) {
            $this->redirect("/dashboard/personnel-admin/scolarite/etudiants/{$idEtudiant}/penalites");
        }

        $montant = (float)$this->post('montant_penalite');
        $motif = $this->post('motif');

        $rules = [
            'montant_penalite' => 'required|numeric|min:0.01',
            'motif' => 'required|string|min:10',
        ];
        $validationData = [
            'montant_penalite' => $montant,
            'motif' => $motif,
        ];
        $this->validator->validate($validationData, $rules);

        if (!$this->validator->isValid()) {
            $this->setFlashMessage('error', implode('<br>', $this->validator->getErrors()));
            $this->redirect("/dashboard/personnel-admin/scolarite/etudiants/{$idEtudiant}/penalites");
        }

        try {
            $this->gestionAcadService->appliquerPenalite($idEtudiant, $montant, $motif);
            $this->setFlashMessage('success', 'Pénalité appliquée avec succès.');
            $this->redirect("/dashboard/personnel-admin/scolarite/etudiants/{$idEtudiant}/penalites");
        } catch (\Exception $e) {
            $this->setFlashMessage('error', 'Erreur application pénalité: ' . $e->getMessage());
            $this->redirect("/dashboard/personnel-admin/scolarite/etudiants/{$idEtudiant}/penalites");
        }
    }

    /**
     * Traite la régularisation d'une pénalité.
     * @param string $idEtudiant L'ID de l'étudiant.
     * @param string $idPenalite L'ID de la pénalité à régulariser.
     */
    public function regularizePenalite(string $idEtudiant, string $idPenalite): void
    {
        $this->requirePermission('TRAIT_PERS_ADMIN_SCOLARITE_PENALITE_REGULARISER'); // Permission spécifique

        if (!$this->isPostRequest()) {
            $this->redirect("/dashboard/personnel-admin/scolarite/etudiants/{$idEtudiant}/penalites");
        }

        try {
            $currentUser = $this->getCurrentUser();
            if (!$currentUser) { throw new ElementNonTrouveException("Utilisateur non trouvé."); }
            $numeroPersonnel = $currentUser['numero_utilisateur'];

            $this->gestionAcadService->regulariserPenalite($idPenalite, $numeroPersonnel);
            $this->setFlashMessage('success', 'Pénalité régularisée avec succès.');
        } catch (\Exception $e) {
            $this->setFlashMessage('error', 'Erreur régularisation pénalité: ' . $e->getMessage());
        }
        $this->redirect("/dashboard/personnel-admin/scolarite/etudiants/{$idEtudiant}/penalites");
    }

    // --- GESTION DES RÉCLAMATIONS (Traitement côté Personnel Administratif) ---

    /**
     * Affiche la liste des réclamations à traiter par le personnel administratif.
     */
    public function listReclamationsToProcess(): void
    {
        $this->requirePermission('TRAIT_PERS_ADMIN_SCOLARITE_RECLAMATION_TRAITER');

        try {
            $page = (int) $this->get('page', 1);
            $limit = 20;
            $filters = ['id_statut_reclamation' => ['operator' => 'in', 'values' => ['RECLAM_RECUE', 'RECLAM_EN_COURS']]];

            $reclamations = $this->reclamationService->recupererToutesReclamations($filters, $page, $limit);
            // Vous pouvez joindre les données de l'étudiant et du personnel traitant ici pour un affichage complet

            $data = [
                'page_title' => 'Réclamations à Traiter',
                'reclamations' => $reclamations,
                'current_page' => $page,
                'items_per_page' => $limit,
                // 'total_items' => $this->reclamationService->countReclamations($filters),
            ];
            $this->render('PersonnelAdministratif/Scolarite/liste_reclamations', $data); // Créer cette vue
        } catch (\Exception $e) {
            $this->setFlashMessage('error', "Erreur chargement réclamations: " . $e->getMessage());
            $this->redirect('/dashboard/personnel-admin/scolarite');
        }
    }

    /**
     * Affiche les détails d'une réclamation et le formulaire de traitement.
     * @param string $idReclamation L'ID de la réclamation.
     */
    public function showReclamationDetails(string $idReclamation): void
    {
        $this->requirePermission('TRAIT_PERS_ADMIN_SCOLARITE_RECLAMATION_TRAITER');

        if ($this->isPostRequest()) {
            $this->handleReclamationTreatment($idReclamation);
        } else {
            try {
                $reclamation = $this->reclamationService->getDetailsReclamation($idReclamation); // Méthode à ajouter au service
                if (!$reclamation) {
                    throw new ElementNonTrouveException("Réclamation non trouvée.");
                }
                // Pour afficher l'étudiant concerné, vous pouvez joindre via reclamationService ou directement l'authService
                // $etudiant = $this->authService->recupererUtilisateurCompletParNumero($reclamation['numero_carte_etudiant']);
                $statutsReclamationRef = $this->configService->listerStatutsReclamation(); // A ajouter au configService

                $data = [
                    'page_title' => 'Détails de la Réclamation',
                    'reclamation' => $reclamation,
                    // 'etudiant' => $etudiant,
                    'statuts_reclamation_ref' => $statutsReclamationRef,
                    'form_action' => "/dashboard/personnel-admin/scolarite/reclamations/{$idReclamation}/process"
                ];
                $this->render('PersonnelAdministratif/Scolarite/details_reclamation', $data); // Créer cette vue
            } catch (\Exception $e) {
                $this->setFlashMessage('error', 'Erreur chargement détails réclamation: ' . $e->getMessage());
                $this->redirect('/dashboard/personnel-admin/scolarite/reclamations');
            }
        }
    }

    /**
     * Traite la soumission du formulaire de traitement de réclamation.
     * @param string $idReclamation L'ID de la réclamation.
     */
    private function handleReclamationTreatment(string $idReclamation): void
    {
        $currentUser = $this->getCurrentUser();
        if (!$currentUser || $currentUser['id_type_utilisateur'] !== 'TYPE_PERS_ADMIN') {
            $this->setFlashMessage('error', "Accès refusé.");
            $this->redirect("/dashboard/personnel-admin/scolarite/reclamations/{$idReclamation}/process");
        }
        $numeroPersonnelTraitant = $currentUser['numero_utilisateur'];

        $newStatut = $this->post('new_statut');
        $reponse = $this->post('reponse_reclamation');

        $rules = [
            'new_statut' => 'required|string|in:RECLAM_RECUE,RECLAM_EN_COURS,RECLAM_REPONDUE,RECLAM_CLOTUREE',
            'reponse_reclamation' => 'nullable|string',
        ];
        if ($newStatut === 'RECLAM_REPONDUE') {
            $rules['reponse_reclamation'] = 'required|string|min:10';
        }
        $validationData = [
            'new_statut' => $newStatut,
            'reponse_reclamation' => $reponse,
        ];
        $this->validator->validate($validationData, $rules);

        if (!$this->validator->isValid()) {
            $this->setFlashMessage('error', implode('<br>', $this->validator->getErrors()));
            $this->redirect("/dashboard/personnel-admin/scolarite/reclamations/{$idReclamation}/process");
        }

        try {
            $this->reclamationService->traiterReclamation($idReclamation, $numeroPersonnelTraitant, $newStatut, $reponse);
            $this->setFlashMessage('success', 'Réclamation traitée avec succès.');
            $this->redirect('/dashboard/personnel-admin/scolarite/reclamations');
        } catch (\Exception $e) {
            $this->setFlashMessage('error', 'Erreur traitement réclamation: ' . $e->getMessage());
            $this->redirect("/dashboard/personnel-admin/scolarite/reclamations/{$idReclamation}/process");
        }
    }


    // --- GESTION DE LA GÉNÉRATION DE DOCUMENTS ---

    /**
     * Affiche le formulaire de génération de documents de scolarité.
     */
    public function showDocumentGenerationForm(): void
    {
        $this->requirePermission('TRAIT_PERS_ADMIN_SCOLARITE_DOCUMENT_GENERER');

        try {
            $etudiants = $this->authService->listerUtilisateursAvecProfils(['id_type_utilisateur' => 'TYPE_ETUD']);
            $anneesAcademiques = $this->configService->listerAnneesAcademiques();
            $typesDocumentsRef = $this->configService->listerTypesDocument(); // Pour les types de documents (Attestation, Bulletin, etc.)

            $data = [
                'page_title' => 'Génération de Documents Scolarité',
                'etudiants' => $etudiants,
                'annees_academiques' => $anneesAcademiques,
                'types_documents_ref' => $typesDocumentsRef,
                'form_action' => '/dashboard/personnel-admin/scolarite/documents/generate'
            ];
            $this->render('PersonnelAdministratif/Scolarite/generation_documents_scolarite', $data);
        } catch (\Exception $e) {
            $this->setFlashMessage('error', 'Erreur chargement formulaire génération documents: ' . $e->getMessage());
            $this->redirect('/dashboard/personnel-admin/scolarite');
        }
    }

    /**
     * Traite la génération d'un document PDF.
     */
    public function generateDocument(): void
    {
        $this->requirePermission('TRAIT_PERS_ADMIN_SCOLARITE_DOCUMENT_GENERER');

        if (!$this->isPostRequest()) {
            $this->redirect('/dashboard/personnel-admin/scolarite/documents');
        }

        $documentType = $this->post('document_type'); // Ex: 'attestation_scolarite', 'bulletin_notes'
        $numeroEtudiant = $this->post('numero_carte_etudiant');
        $idAnneeAcademique = $this->post('id_annee_academique');

        $rules = [
            'document_type' => 'required|string',
            'numero_carte_etudiant' => 'required|string|max:50',
            'id_annee_academique' => 'nullable|string|max:50', // Année requise pour bulletins
        ];
        $validationData = [
            'document_type' => $documentType,
            'numero_carte_etudiant' => $numeroEtudiant,
            'id_annee_academique' => $idAnneeAcademique,
        ];
        $this->validator->validate($validationData, $rules);

        if (!$this->validator->isValid()) {
            $this->setFlashMessage('error', implode('<br>', $this->validator->getErrors()));
            $this->redirect('/dashboard/personnel-admin/scolarite/documents');
        }

        try {
            $filePath = '';
            if ($documentType === 'attestation_scolarite') {
                $filePath = $this->documentGeneratorService->genererAttestationScolarite($numeroEtudiant, 'inscription'); // Le type d'attestation peut être dynamique
            } elseif ($documentType === 'bulletin_notes') {
                if (empty($idAnneeAcademique)) {
                    throw new ValidationException("L'année académique est requise pour générer un bulletin de notes.");
                }
                $filePath = $this->documentGeneratorService->genererBulletinNotes($numeroEtudiant, $idAnneeAcademique);
            } else {
                throw new OperationImpossibleException("Type de document non supporté.");
            }

            $this->setFlashMessage('success', 'Document généré avec succès. Vous pouvez le télécharger.');
            // Optionnel: Rediriger vers la page du document généré pour le télécharger
            // Ou fournir un lien de téléchargement direct
            $this->redirect('/dashboard/personnel-admin/scolarite/documents');
        } catch (ValidationException $e) {
            $this->setFlashMessage('error', $e->getMessage());
            $this->redirect('/dashboard/personnel-admin/scolarite/documents');
        } catch (\Exception $e) {
            $this->setFlashMessage('error', 'Erreur lors de la génération du document: ' . $e->getMessage());
            $this->redirect('/dashboard/personnel-admin/scolarite/documents');
        }
    }

    // Les méthodes create(), update(), delete() génériques du template initial sont à supprimer.
    /*
    public function create(): void {}
    public function update($id): void {}
    public function delete($id): void {}
    */
}