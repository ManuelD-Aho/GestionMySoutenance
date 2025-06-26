<?php
namespace App\Backend\Controller\Administration;

use App\Backend\Controller\BaseController;
use App\Backend\Service\Authentication\ServiceAuthentication;
use App\Backend\Service\Permissions\ServicePermissions;
use App\Backend\Util\FormValidator;
use App\Backend\Service\GestionAcademique\ServiceGestionAcademique; // Importer le service
use App\Backend\Service\ConfigurationSysteme\ServiceConfigurationSysteme; // Pour lister années académiques, niveaux etc.
use App\Backend\Exception\ElementNonTrouveException;
use App\Backend\Exception\OperationImpossibleException;
use App\Backend\Exception\DoublonException;
use App\Backend\Exception\ValidationException; // Pour les erreurs de validation du formulaire

class GestionAcadController extends BaseController
{
    private ServiceGestionAcademique $gestionAcadService;
    private ServiceConfigurationSysteme $configService; // Pour récupérer des listes de référence (années, niveaux, etc.)

    public function __construct(
        ServiceAuthentication       $authService,
        ServicePermissions          $permissionService,
        FormValidator               $validator,
        ServiceGestionAcademique    $gestionAcadService, // Injection
        ServiceConfigurationSysteme $configService // Injection
    ) {
        parent::__construct($authService, $permissionService, $validator);
        $this->gestionAcadService = $gestionAcadService;
        $this->configService = $configService;
    }

    /**
     * Affiche le tableau de bord ou la liste principale de la gestion académique.
     * Peut rediriger vers des sous-sections (inscriptions, notes).
     */
    public function index(): void
    {
        $this->requirePermission('TRAIT_ADMIN_GESTION_ACAD_ACCEDER'); // Permission générale

        $data = [
            'page_title' => 'Gestion Académique',
            'sections' => [
                ['label' => 'Gestion des Inscriptions', 'url' => '/dashboard/admin/gestion-acad/inscriptions'],
                ['label' => 'Gestion des Notes', 'url' => '/dashboard/admin/gestion-acad/notes'],
                // Ajoutez d'autres sections comme stages, carrières enseignants si elles ont leurs propres vues
            ]
        ];
        $this->render('Administration/GestionAcad/index', $data); // Créer une vue index.php dans ce dossier si elle n'existe pas
    }

    // --- GESTION DES INSCRIPTIONS ---

    /**
     * Affiche la liste des inscriptions.
     */
    public function listInscriptions(): void
    {
        $this->requirePermission('TRAIT_ADMIN_GESTION_ACAD_INSCRIPTION_LISTER');

        try {
            $page = (int) $this->getRequestData('page', 1);
            $limit = 20; // Nombre d'éléments par page

            $inscriptions = $this->gestionAcadService->listerInscriptionsAdministratives([], $page, $limit);
            // Pour afficher des détails lisibles, vous devrez récupérer les libellés via des jointures dans le service,
            // ou en faisant des appels supplémentaires ici (moins performant si beaucoup d'items).

            $data = [
                'page_title' => 'Liste des Inscriptions',
                'inscriptions' => $inscriptions,
                'current_page' => $page,
                'items_per_page' => $limit,
                // 'total_items' => $this->gestionAcadService->countInscriptionsAdministratives([]), // A créer dans le service
            ];
            $this->render('Administration/GestionAcad/liste_inscriptions', $data);
        } catch (\Exception $e) {
            $this->setFlashMessage('error', "Erreur lors du chargement des inscriptions: " . $e->getMessage());
            $this->redirect('/dashboard/admin/gestion-acad');
        }
    }

    /**
     * Affiche le formulaire de création d'inscription ou la traite.
     */
    public function createInscription(): void
    {
        $this->requirePermission('TRAIT_ADMIN_GESTION_ACAD_INSCRIPTION_CREER');

        if ($this->isPostRequest()) {
            $this->handleCreateInscription();
        } else {
            try {
                // Données pour les listes déroulantes du formulaire
                $etudiants = $this->authService->listerUtilisateursAvecProfils(['id_type_utilisateur' => 'TYPE_ETUD']);
                $niveauxEtude = $this->configService->listerNiveauxEtude(); // A ajouter dans configService
                $anneesAcademiques = $this->configService->listerAnneesAcademiques();
                $statutsPaiement = $this->configService->listerStatutsPaiement(); // A ajouter dans configService

                $data = [
                    'page_title' => 'Ajouter une Inscription',
                    'etudiants' => $etudiants,
                    'niveaux_etude' => $niveauxEtude,
                    'annees_academiques' => $anneesAcademiques,
                    'statuts_paiement' => $statutsPaiement,
                    'form_action' => '/dashboard/admin/gestion-acad/inscriptions/create'
                ];
                $this->render('Administration/GestionAcad/form_inscription', $data);
            } catch (\Exception $e) {
                $this->setFlashMessage('error', 'Erreur chargement formulaire: ' . $e->getMessage());
                $this->redirect('/dashboard/admin/gestion-acad/inscriptions');
            }
        }
    }

    /**
     * Traite la soumission du formulaire de création d'inscription.
     */
    private function handleCreateInscription(): void
    {
        $inscriptionData = [
            'numero_carte_etudiant' => $this->getRequestData('numero_carte_etudiant'),
            'id_niveau_etude' => $this->getRequestData('id_niveau_etude'),
            'id_annee_academique' => $this->getRequestData('id_annee_academique'),
            'montant_inscription' => (float)$this->getRequestData('montant_inscription'),
            'id_statut_paiement' => $this->getRequestData('id_statut_paiement'),
            'numero_recu_paiement' => $this->getRequestData('numero_recu_paiement') ?: null,
        ];

        $rules = [
            'numero_carte_etudiant' => 'required|string|max:50',
            'id_niveau_etude' => 'required|string|max:50',
            'id_annee_academique' => 'required|string|max:50',
            'montant_inscription' => 'required|numeric|min:0',
            'id_statut_paiement' => 'required|string|max:50',
            'numero_recu_paiement' => 'nullable|string|max:50',
        ];

        $this->validator->validate($inscriptionData, $rules);

        if (!$this->validator->isValid()) {
            $this->setFlashMessage('error', implode('<br>', $this->validator->getErrors()));
            $this->redirect('/dashboard/admin/gestion-acad/inscriptions/create');
        }

        try {
            $this->gestionAcadService->creerInscriptionAdministrative(
                $inscriptionData['numero_carte_etudiant'],
                $inscriptionData['id_niveau_etude'],
                $inscriptionData['id_annee_academique'],
                $inscriptionData['montant_inscription'],
                $inscriptionData['id_statut_paiement'],
                $inscriptionData['numero_recu_paiement']
            );
            $this->setFlashMessage('success', 'Inscription ajoutée avec succès.');
            $this->redirect('/dashboard/admin/gestion-acad/inscriptions');
        } catch (DoublonException $e) {
            $this->setFlashMessage('error', 'Erreur: ' . $e->getMessage());
            $this->redirect('/dashboard/admin/gestion-acad/inscriptions/create');
        } catch (OperationImpossibleException $e) {
            $this->setFlashMessage('error', 'Opération impossible: ' . $e->getMessage());
            $this->redirect('/dashboard/admin/gestion-acad/inscriptions/create');
        } catch (ElementNonTrouveException $e) {
            $this->setFlashMessage('error', 'Donnée de référence manquante: ' . $e->getMessage());
            $this->redirect('/dashboard/admin/gestion-acad/inscriptions/create');
        } catch (\Exception $e) {
            $this->setFlashMessage('error', 'Une erreur inattendue est survenue: ' . $e->getMessage());
            $this->redirect('/dashboard/admin/gestion-acad/inscriptions/create');
        }
    }

    /**
     * Affiche le formulaire de modification d'inscription ou la traite.
     * @param string $numeroCarteEtudiant L'ID de l'étudiant.
     * @param string $idNiveauEtude L'ID du niveau d'étude.
     * @param string $idAnneeAcademique L'ID de l'année académique.
     */
    public function editInscription(string $numeroCarteEtudiant, string $idNiveauEtude, string $idAnneeAcademique): void
    {
        $this->requirePermission('TRAIT_ADMIN_GESTION_ACAD_INSCRIPTION_MODIFIER');

        if ($this->isPostRequest()) {
            $this->handleEditInscription($numeroCarteEtudiant, $idNiveauEtude, $idAnneeAcademique);
        } else {
            try {
                $inscription = $this->gestionAcadService->listerInscriptionsAdministratives([
                    'numero_carte_etudiant' => $numeroCarteEtudiant,
                    'id_niveau_etude' => $idNiveauEtude,
                    'id_annee_academique' => $idAnneeAcademique
                ]);
                if (empty($inscription)) {
                    throw new ElementNonTrouveException("Inscription non trouvée.");
                }
                $inscription = $inscription[0]; // Prend la première (unique par PK)

                $etudiants = $this->authService->listerUtilisateursAvecProfils(['id_type_utilisateur' => 'TYPE_ETUD']);
                $niveauxEtude = $this->configService->listerNiveauxEtude(); // A ajouter dans configService
                $anneesAcademiques = $this->configService->listerAnneesAcademiques();
                $statutsPaiement = $this->configService->listerStatutsPaiement(); // A ajouter dans configService
                $decisionsPassage = $this->configService->listerDecisionsPassage(); // A ajouter dans configService


                $data = [
                    'page_title' => 'Modifier Inscription',
                    'inscription' => $inscription,
                    'etudiants' => $etudiants,
                    'niveaux_etude' => $niveauxEtude,
                    'annees_academiques' => $anneesAcademiques,
                    'statuts_paiement' => $statutsPaiement,
                    'decisions_passage' => $decisionsPassage,
                    'form_action' => "/dashboard/admin/gestion-acad/inscriptions/{$numeroCarteEtudiant}/{$idNiveauEtude}/{$idAnneeAcademique}/edit"
                ];
                $this->render('Administration/GestionAcad/form_inscription', $data);
            } catch (ElementNonTrouveException $e) {
                $this->setFlashMessage('error', $e->getMessage());
                $this->redirect('/dashboard/admin/gestion-acad/inscriptions');
            } catch (\Exception $e) {
                $this->setFlashMessage('error', 'Erreur: ' . $e->getMessage());
                $this->redirect('/dashboard/admin/gestion-acad/inscriptions');
            }
        }
    }

    /**
     * Traite la soumission du formulaire de modification d'inscription.
     * @param string $numeroCarteEtudiant L'ID de l'étudiant.
     * @param string $idNiveauEtude L'ID du niveau d'étude.
     * @param string $idAnneeAcademique L'ID de l'année académique.
     */
    private function handleEditInscription(string $numeroCarteEtudiant, string $idNiveauEtude, string $idAnneeAcademique): void
    {
        $inscriptionData = [
            'montant_inscription' => (float)$this->getRequestData('montant_inscription'),
            'id_statut_paiement' => $this->getRequestData('id_statut_paiement'),
            'numero_recu_paiement' => $this->getRequestData('numero_recu_paiement') ?: null,
            'id_decision_passage' => $this->getRequestData('id_decision_passage') ?: null,
        ];

        $rules = [
            'montant_inscription' => 'required|numeric|min:0',
            'id_statut_paiement' => 'required|string|max:50',
            'numero_recu_paiement' => 'nullable|string|max:50',
            'id_decision_passage' => 'nullable|string|max:50',
        ];

        $this->validator->validate($inscriptionData, $rules);

        if (!$this->validator->isValid()) {
            $this->setFlashMessage('error', implode('<br>', $this->validator->getErrors()));
            $this->redirect("/dashboard/admin/gestion-acad/inscriptions/{$numeroCarteEtudiant}/{$idNiveauEtude}/{$idAnneeAcademique}/edit");
        }

        try {
            $this->gestionAcadService->mettreAJourInscriptionAdministrative(
                $numeroCarteEtudiant,
                $idNiveauEtude,
                $idAnneeAcademique,
                $inscriptionData
            );
            $this->setFlashMessage('success', 'Inscription modifiée avec succès.');
            $this->redirect('/dashboard/admin/gestion-acad/inscriptions');
        } catch (ElementNonTrouveException $e) {
            $this->setFlashMessage('error', $e->getMessage());
            $this->redirect('/dashboard/admin/gestion-acad/inscriptions');
        } catch (DoublonException $e) {
            $this->setFlashMessage('error', 'Erreur: ' . $e->getMessage()); // Pour numéro_recu_paiement dupliqué
            $this->redirect("/dashboard/admin/gestion-acad/inscriptions/{$numeroCarteEtudiant}/{$idNiveauEtude}/{$idAnneeAcademique}/edit");
        } catch (OperationImpossibleException $e) {
            $this->setFlashMessage('error', 'Opération impossible: ' . $e->getMessage());
            $this->redirect("/dashboard/admin/gestion-acad/inscriptions/{$numeroCarteEtudiant}/{$idNiveauEtude}/{$idAnneeAcademique}/edit");
        } catch (\Exception $e) {
            $this->setFlashMessage('error', 'Une erreur inattendue est survenue: ' . $e->getMessage());
            $this->redirect("/dashboard/admin/gestion-acad/inscriptions/{$numeroCarteEtudiant}/{$idNiveauEtude}/{$idAnneeAcademique}/edit");
        }
    }

    /**
     * Supprime une inscription.
     * @param string $numeroCarteEtudiant L'ID de l'étudiant.
     * @param string $idNiveauEtude L'ID du niveau d'étude.
     * @param string $idAnneeAcademique L'ID de l'année académique.
     */
    public function deleteInscription(string $numeroCarteEtudiant, string $idNiveauEtude, string $idAnneeAcademique): void
    {
        $this->requirePermission('TRAIT_ADMIN_GESTION_ACAD_INSCRIPTION_SUPPRIMER');

        try {
            // Pour la suppression, on utilise le modèle Inscrire directement car c'est une opération atomique sur une entité.
            // Le service pourrait avoir une méthode `supprimerInscriptionAdministrative()` qui englobe ça.
            $pdo = $this->authService->getUtilisateurModel()->getDb(); // Accès à PDO via un service/modèle existant
            $inscrireModel = new \App\Backend\Model\Inscrire($pdo);
            if (!$inscrireModel->supprimerParCleComposite($numeroCarteEtudiant, $idNiveauEtude, $idAnneeAcademique)) {
                throw new OperationImpossibleException("Échec de la suppression de l'inscription.");
            }
            $this->setFlashMessage('success', 'Inscription supprimée avec succès.');
        } catch (ElementNonTrouveException $e) {
            $this->setFlashMessage('error', $e->getMessage());
        } catch (OperationImpossibleException $e) {
            $this->setFlashMessage('error', 'Impossible de supprimer cette inscription : ' . $e->getMessage());
        } catch (\Exception $e) {
            $this->setFlashMessage('error', 'Une erreur inattendue est survenue: ' . $e->getMessage());
        }
        $this->redirect('/dashboard/admin/gestion-acad/inscriptions');
    }

    // --- GESTION DES NOTES ---

    /**
     * Affiche la liste des notes.
     */
    public function listNotes(): void
    {
        $this->requirePermission('TRAIT_ADMIN_GESTION_ACAD_NOTE_LISTER');

        try {
            $page = (int) $this->getRequestData('page', 1);
            $limit = 20; // Nombre d'éléments par page

            $notes = $this->gestionAcadService->listerNotes([], $page, $limit); // A créer dans le service
            // Pour afficher des détails, joindre étudiant, ecue etc.

            $data = [
                'page_title' => 'Liste des Notes',
                'notes' => $notes,
                'current_page' => $page,
                'items_per_page' => $limit,
                // 'total_items' => $this->gestionAcadService->countNotes([]), // A créer dans le service
            ];
            $this->render('Administration/GestionAcad/liste_notes', $data);
        } catch (\Exception $e) {
            $this->setFlashMessage('error', "Erreur lors du chargement des notes: " . $e->getMessage());
            $this->redirect('/dashboard/admin/gestion-acad');
        }
    }

    /**
     * Affiche le formulaire de création/modification de note ou la traite.
     * @param string|null $numeroCarteEtudiant L'ID de l'étudiant pour modification.
     * @param string|null $idEcue L'ID de l'ECUE pour modification.
     */
    public function handleNoteForm(string $numeroCarteEtudiant = null, string $idEcue = null): void
    {
        $isEdit = ($numeroCarteEtudiant !== null && $idEcue !== null);
        $permission = $isEdit ? 'TRAIT_ADMIN_GESTION_ACAD_NOTE_MODIFIER' : 'TRAIT_ADMIN_GESTION_ACAD_NOTE_CREER';
        $this->requirePermission($permission);

        if ($this->isPostRequest()) {
            $this->handleSubmitNote($numeroCarteEtudiant, $idEcue);
        } else {
            try {
                $note = null;
                if ($isEdit) {
                    $evaluations = $this->gestionAcadService->listerNotes([
                        'numero_carte_etudiant' => $numeroCarteEtudiant,
                        'id_ecue' => $idEcue
                    ]);
                    if (empty($evaluations)) {
                        throw new ElementNonTrouveException("Note non trouvée.");
                    }
                    $note = $evaluations[0];
                }

                $etudiants = $this->authService->listerUtilisateursAvecProfils(['id_type_utilisateur' => 'TYPE_ETUD']);
                $ecues = $this->configService->listerEcues(); // A ajouter dans configService (si non déjà là)

                $data = [
                    'page_title' => $isEdit ? 'Modifier Note' : 'Ajouter Note',
                    'note' => $note,
                    'etudiants' => $etudiants,
                    'ecues' => $ecues,
                    'form_action' => $isEdit ? "/dashboard/admin/gestion-acad/notes/{$numeroCarteEtudiant}/{$idEcue}/edit" : "/dashboard/admin/gestion-acad/notes/create"
                ];
                $this->render('Administration/GestionAcad/form_note', $data);
            } catch (\Exception $e) {
                $this->setFlashMessage('error', 'Erreur chargement formulaire: ' . $e->getMessage());
                $this->redirect('/dashboard/admin/gestion-acad/notes');
            }
        }
    }

    /**
     * Traite la soumission du formulaire de note.
     * @param string|null $numeroCarteEtudiant L'ID de l'étudiant (pour modification).
     * @param string|null $idEcue L'ID de l'ECUE (pour modification).
     */
    private function handleSubmitNote(?string $numeroCarteEtudiant = null, ?string $idEcue = null): void
    {
        $isEdit = ($numeroCarteEtudiant !== null && $idEcue !== null);

        $noteData = [
            'numero_carte_etudiant' => $this->getRequestData('numero_carte_etudiant'),
            'id_ecue' => $this->getRequestData('id_ecue'),
            'note' => (float)$this->getRequestData('note'),
        ];

        // Si c'est une création, les IDs viennent du formulaire. Si c'est une modification, ils viennent de l'URL.
        if ($isEdit) {
            $noteData['numero_carte_etudiant'] = $numeroCarteEtudiant;
            $noteData['id_ecue'] = $idEcue;
        }

        $rules = [
            'numero_carte_etudiant' => 'required|string|max:50',
            'id_ecue' => 'required|string|max:50',
            'note' => 'required|numeric|min:0|max:20',
        ];

        $this->validator->validate($noteData, $rules);

        if (!$this->validator->isValid()) {
            $this->setFlashMessage('error', implode('<br>', $this->validator->getErrors()));
            $this->redirect($isEdit ? "/dashboard/admin/gestion-acad/notes/{$numeroCarteEtudiant}/{$idEcue}/edit" : "/dashboard/admin/gestion-acad/notes/create");
        }

        try {
            $this->gestionAcadService->enregistrerNoteEcue(
                $noteData['numero_carte_etudiant'],
                $noteData['id_ecue'],
                $noteData['note']
            );
            $this->setFlashMessage('success', 'Note ' . ($isEdit ? 'modifiée' : 'ajoutée') . ' avec succès.');
            $this->redirect('/dashboard/admin/gestion-acad/notes');
        } catch (ElementNonTrouveException $e) {
            $this->setFlashMessage('error', $e->getMessage());
            $this->redirect($isEdit ? "/dashboard/admin/gestion-acad/notes/{$numeroCarteEtudiant}/{$idEcue}/edit" : "/dashboard/admin/gestion-acad/notes/create");
        } catch (OperationImpossibleException $e) {
            $this->setFlashMessage('error', 'Opération impossible: ' . $e->getMessage());
            $this->redirect($isEdit ? "/dashboard/admin/gestion-acad/notes/{$numeroCarteEtudiant}/{$idEcue}/edit" : "/dashboard/admin/gestion-acad/notes/create");
        } catch (\Exception $e) {
            $this->setFlashMessage('error', 'Une erreur inattendue est survenue: ' . $e->getMessage());
            $this->redirect($isEdit ? "/dashboard/admin/gestion-acad/notes/{$numeroCarteEtudiant}/{$idEcue}/edit" : "/dashboard/admin/gestion-acad/notes/create");
        }
    }

    /**
     * Supprime une note.
     * @param string $numeroCarteEtudiant L'ID de l'étudiant.
     * @param string $idEcue L'ID de l'ECUE.
     */
    public function deleteNote(string $numeroCarteEtudiant, string $idEcue): void
    {
        $this->requirePermission('TRAIT_ADMIN_GESTION_ACAD_NOTE_SUPPRIMER');

        try {
            // Utiliser le modèle Evaluer directement pour la suppression, ou ajouter une méthode au service
            $pdo = $this->authService->getUtilisateurModel()->getDb(); // Accès à PDO
            $evaluerModel = new \App\Backend\Model\Evaluer($pdo);
            if (!$evaluerModel->supprimerEvaluationParCles($numeroCarteEtudiant, $idEcue)) {
                throw new OperationImpossibleException("Échec de la suppression de la note.");
            }
            $this->setFlashMessage('success', 'Note supprimée avec succès.');
        } catch (ElementNonTrouveException $e) {
            $this->setFlashMessage('error', $e->getMessage());
        } catch (OperationImpossibleException $e) {
            $this->setFlashMessage('error', 'Impossible de supprimer cette note : ' . $e->getMessage());
        } catch (\Exception $e) {
            $this->setFlashMessage('error', 'Une erreur inattendue est survenue: ' . $e->getMessage());
        }
        $this->redirect('/dashboard/admin/gestion-acad/notes');
    }

    // Les méthodes create(), update(), delete() génériques du template initial sont à supprimer
    // car les fonctionnalités spécifiques (inscriptions, notes) sont traitées par des méthodes dédiées.
    /*
    public function create(): void {}
    public function update($id): void {}
    public function delete($id): void {}
    */
}