<?php
namespace App\Backend\Controller\Etudiant;

use App\Backend\Controller\BaseController;
use App\Backend\Service\Authentication\ServiceAuthentication;
use App\Backend\Service\Permissions\ServicePermissions;
use App\Backend\Util\FormValidator;
use App\Backend\Service\Rapport\ServiceRapport; // Importer le service
use App\Backend\Service\GestionAcademique\ServiceGestionAcademique; // Pour vérifier l'éligibilité
use App\Backend\Service\ConfigurationSysteme\ServiceConfigurationSysteme; // Pour l'année académique
use App\Backend\Exception\ElementNonTrouveException;
use App\Backend\Exception\OperationImpossibleException;
use App\Backend\Exception\DoublonException;
use App\Backend\Exception\ValidationException;

class RapportController extends BaseController
{
    private ServiceRapport $rapportService;
    private ServiceGestionAcademique $gestionAcadService;
    private ServiceConfigurationSysteme $configService;

    public function __construct(
        ServiceAuthentication       $authService,
        ServicePermissions          $permissionService,
        FormValidator               $validator,
        ServiceRapport              $rapportService,
        ServiceGestionAcademique    $gestionAcadService,
        ServiceConfigurationSysteme $configService
    ) {
        parent::__construct($authService, $permissionService, $validator);
        $this->rapportService = $rapportService;
        $this->gestionAcadService = $gestionAcadService;
        $this->configService = $configService;
    }

    /**
     * Affiche la page de suivi du rapport de l'étudiant.
     * @param string|null $id Rapport ID si spécifique, sinon tente de trouver le plus récent/actif.
     */
    public function index(?string $id = null): void
    {
        $this->requirePermission('TRAIT_ETUDIANT_RAPPORT_SUIVRE'); // Exiger la permission

        try {
            $currentUser = $this->getCurrentUser();
            if (!$currentUser || $currentUser['id_type_utilisateur'] !== 'TYPE_ETUD') {
                throw new OperationImpossibleException("Accès refusé. Non étudiant.");
            }
            $numeroCarteEtudiant = $currentUser['numero_utilisateur'];

            $rapportId = $id;
            if (!$rapportId) {
                // Tenter de trouver le rapport le plus récent ou en cours de l'étudiant
                $rapports = $this->rapportService->listerRapportsParCriteres(['numero_carte_etudiant' => $numeroCarteEtudiant], ['id_rapport_etudiant'], 'AND', 'date_derniere_modif DESC', 1);
                $rapportId = $rapports[0]['id_rapport_etudiant'] ?? null;
            }

            $rapport = null;
            if ($rapportId) {
                $rapport = $this->rapportService->recupererInformationsRapportComplet($rapportId);
                // Vérifier que le rapport appartient bien à l'étudiant connecté
                if ($rapport && $rapport['numero_carte_etudiant'] !== $numeroCarteEtudiant) {
                    throw new OperationImpossibleException("Vous n'êtes pas autorisé à consulter ce rapport.");
                }
            }

            $data = [
                'page_title' => 'Suivi de mon Rapport',
                'rapport' => $rapport,
                'is_eligible_for_submission' => false // Sera mis à jour si pertinent
            ];

            // Vérifier l'éligibilité à la soumission pour afficher le bouton 'Soumettre' ou 'Modifier'
            $anneeAcademiqueActive = $this->configService->recupererAnneeAcademiqueActive();
            if ($anneeAcademiqueActive) {
                $data['is_eligible_for_submission'] = $this->gestionAcadService->estEtudiantEligibleSoumission($numeroCarteEtudiant, $anneeAcademiqueActive['id_annee_academique']);
            }

            $this->render('Etudiant/Rapport/suivi_rapport', $data);
        } catch (\Exception $e) {
            $this->setFlashMessage('error', "Erreur lors du chargement du suivi de votre rapport: " . $e->getMessage());
            $this->redirect('/dashboard/etudiant');
        }
    }

    /**
     * Affiche le formulaire de soumission initiale ou de modification d'un brouillon de rapport.
     * @param string|null $id L'ID du rapport à modifier (s'il est en brouillon), ou null pour un nouveau.
     */
    public function createOrEditDraft(?string $id = null): void
    {
        $this->requirePermission('TRAIT_ETUDIANT_RAPPORT_SOUMETTRE'); // Permission de soumettre/modifier un rapport

        try {
            $currentUser = $this->getCurrentUser();
            if (!$currentUser || $currentUser['id_type_utilisateur'] !== 'TYPE_ETUD') {
                throw new OperationImpossibleException("Accès refusé. Non étudiant.");
            }
            $numeroCarteEtudiant = $currentUser['numero_utilisateur'];

            $rapport = null;
            $isEditing = false;
            if ($id) {
                $rapport = $this->rapportService->recupererInformationsRapportComplet($id);
                if (!$rapport || $rapport['numero_carte_etudiant'] !== $numeroCarteEtudiant) {
                    throw new ElementNonTrouveException("Rapport non trouvé ou non autorisé.");
                }
                if ($rapport['id_statut_rapport'] !== 'RAP_BROUILLON') {
                    throw new OperationImpossibleException("Ce rapport ne peut être modifié que s'il est en brouillon.");
                }
                $isEditing = true;
            } else {
                // Vérifier si l'étudiant a déjà un rapport non finalisé (brouillon, soumis, en commission, etc.)
                $existingRapports = $this->rapportService->listerRapportsParCriteres([
                    'numero_carte_etudiant' => $numeroCarteEtudiant,
                    'id_statut_rapport' => ['operator' => 'not in', 'values' => ['RAP_VALID', 'RAP_REFUSE', 'RAP_ARCHIVE', 'RAP_FINAL_CLOTURE']] // Assurez-vous d'inclure tous les statuts "terminés" ou "clôturés"
                ]);
                // If you want to allow only one draft at a time for submission
                // $currentDraft = $this->rapportService->getMostRecentRapportId($numeroCarteEtudiant); // Need a method to get active draft
                // if ($currentDraft && $this->rapportService->recupererInformationsRapportComplet($currentDraft)['id_statut_rapport'] === 'RAP_BROUILLON') {
                //     $this->setFlashMessage('warning', 'Vous avez déjà un brouillon de rapport en cours. Vous pouvez le modifier.');
                //     $this->redirect("/dashboard/etudiant/rapport/create-edit-draft/{$currentDraft}");
                //     return;
                // }
            }

            $data = [
                'page_title' => $isEditing ? 'Modifier mon Rapport' : 'Soumettre mon Rapport',
                'rapport' => $rapport,
                'form_action' => $isEditing ? "/dashboard/etudiant/rapport/{$id}/save-submit" : "/dashboard/etudiant/rapport/save-submit"
            ];
            $this->render('Etudiant/Rapport/soumettre_rapport', $data);
        } catch (\Exception $e) {
            $this->setFlashMessage('error', "Erreur lors du chargement du formulaire de rapport: " . $e->getMessage());
            $this->redirect('/dashboard/etudiant/rapport');
        }
    }

    /**
     * Gère la soumission du formulaire (sauvegarde brouillon ou soumission finale).
     * @param string|null $id L'ID du rapport (si modification d'un brouillon).
     */
    public function saveOrSubmit(?string $id = null): void
    {
        $this->requirePermission('TRAIT_ETUDIANT_RAPPORT_SOUMETTRE');

        if (!$this->isPostRequest()) {
            $this->redirect($id ? "/dashboard/etudiant/rapport/create-edit-draft/{$id}" : "/dashboard/etudiant/rapport/create-edit-draft");
        }

        $currentUser = $this->getCurrentUser();
        if (!$currentUser || $currentUser['id_type_utilisateur'] !== 'TYPE_ETUD') {
            $this->setFlashMessage('error', "Accès refusé.");
            $this->redirect('/dashboard/etudiant/rapport');
        }
        $numeroCarteEtudiant = $currentUser['numero_utilisateur'];

        $actionType = $this->getRequestData('action_type'); // 'save_draft' ou 'submit_final'

        $metadonnees = [
            'libelle_rapport_etudiant' => $this->getRequestData('libelle_rapport_etudiant'),
            'theme' => $this->getRequestData('theme'),
            'resume' => $this->getRequestData('resume'),
            'nombre_pages' => (int)$this->getRequestData('nombre_pages'),
            'numero_attestation_stage' => $this->getRequestData('numero_attestation_stage'),
        ];

        // Les sections du rapport viendraient d'un éditeur WYSIWYG, par exemple:
        $sectionsContenu = [
            'introduction' => $this->getRequestData('section_introduction', ''),
            'corps_rapport' => $this->getRequestData('section_corps_rapport', ''),
            'conclusion' => $this->getRequestData('section_conclusion', ''),
            // ... autres sections
        ];

        $rulesMetadonnees = [
            'libelle_rapport_etudiant' => 'required|string|min:10|max:255',
            'theme' => 'required|string|min:5|max:255',
            'resume' => 'required|string|min:20|max:1000',
            'nombre_pages' => 'required|integer|min:1',
            'numero_attestation_stage' => 'required|string|max:100', // Si obligatoire
        ];
        $this->validator->validate($metadonnees, $rulesMetadonnees);

        if (!$this->validator->isValid()) {
            $this->setFlashMessage('error', implode('<br>', $this->validator->getErrors()));
            $this->redirect($id ? "/dashboard/etudiant/rapport/create-edit-draft/{$id}" : "/dashboard/etudiant/rapport/create-edit-draft");
        }

        try {
            $rapportId = $this->rapportService->creerOuMettreAJourBrouillonRapport($numeroCarteEtudiant, $metadonnees, $sectionsContenu);

            if ($actionType === 'submit_final') {
                // Vérifier l'éligibilité finale juste avant la soumission
                $anneeAcademiqueActive = $this->configService->recupererAnneeAcademiqueActive();
                if (!$anneeAcademiqueActive || !$this->gestionAcadService->estEtudiantEligibleSoumission($numeroCarteEtudiant, $anneeAcademiqueActive['id_annee_academique'])) {
                    throw new OperationImpossibleException("Vous n'êtes pas éligible à la soumission du rapport. Veuillez vérifier votre inscription, stage ou pénalités.");
                }
                $this->rapportService->soumettreRapportPourVerification($rapportId);
                $this->setFlashMessage('success', 'Rapport soumis avec succès pour vérification !');
                $this->redirect('/dashboard/etudiant/rapport');
            } else { // 'save_draft' ou action par défaut
                $this->setFlashMessage('success', 'Brouillon du rapport sauvegardé avec succès.');
                $this->redirect('/dashboard/etudiant/rapport'); // Rediriger vers le suivi du rapport
            }
        } catch (ElementNonTrouveException $e) {
            $this->setFlashMessage('error', $e->getMessage());
            $this->redirect($id ? "/dashboard/etudiant/rapport/create-edit-draft/{$id}" : "/dashboard/etudiant/rapport/create-edit-draft");
        } catch (OperationImpossibleException $e) {
            $this->setFlashMessage('error', 'Impossible de ' . ($actionType === 'submit_final' ? 'soumettre' : 'sauvegarder') . ' le rapport : ' . $e->getMessage());
            $this->redirect($id ? "/dashboard/etudiant/rapport/create-edit-draft/{$id}" : "/dashboard/etudiant/rapport/create-edit-draft");
        } catch (\Exception $e) {
            $this->setFlashMessage('error', 'Une erreur inattendue est survenue: ' . $e->getMessage());
            error_log("Rapport save/submit error: " . $e->getMessage());
            $this->redirect($id ? "/dashboard/etudiant/rapport/create-edit-draft/{$id}" : "/dashboard/etudiant/rapport/create-edit-draft");
        }
    }

    /**
     * Affiche le formulaire de soumission des corrections pour un rapport.
     * @param string $id L'ID du rapport à corriger.
     */
    public function showCorrectionForm(string $id): void
    {
        $this->requirePermission('TRAIT_ETUDIANT_RAPPORT_SOUMETTRE_CORRECTIONS'); // Permission pour soumettre des corrections

        try {
            $currentUser = $this->getCurrentUser();
            if (!$currentUser || $currentUser['id_type_utilisateur'] !== 'TYPE_ETUD') {
                throw new OperationImpossibleException("Accès refusé. Non étudiant.");
            }
            $numeroCarteEtudiant = $currentUser['numero_utilisateur'];

            $rapport = $this->rapportService->recupererInformationsRapportComplet($id);
            if (!$rapport || $rapport['numero_carte_etudiant'] !== $numeroCarteEtudiant) {
                throw new ElementNonTrouveException("Rapport non trouvé ou non autorisé.");
            }
            // Le rapport doit être au statut 'RAP_NON_CONF' ou 'RAP_CORRECT'
            if (!in_array($rapport['id_statut_rapport'], ['RAP_NON_CONF', 'RAP_CORRECT'])) {
                throw new OperationImpossibleException("Ce rapport n'est pas en attente de corrections.");
            }

            $data = [
                'page_title' => 'Soumettre les Corrections du Rapport',
                'rapport' => $rapport,
                'form_action' => "/dashboard/etudiant/rapport/{$id}/submit-corrections"
            ];
            $this->render('Etudiant/Rapport/soumettre_corrections', $data);
        } catch (\Exception $e) {
            $this->setFlashMessage('error', "Erreur lors du chargement du formulaire de corrections: " . $e->getMessage());
            $this->redirect("/dashboard/etudiant/rapport/{$id}"); // Rediriger vers le suivi du rapport
        }
    }

    /**
     * Traite la soumission des corrections d'un rapport par l'étudiant.
     * @param string $id L'ID du rapport.
     */
    public function submitCorrections(string $id): void
    {
        $this->requirePermission('TRAIT_ETUDIANT_RAPPORT_SOUMETTRE_CORRECTIONS');

        if (!$this->isPostRequest()) {
            $this->redirect("/dashboard/etudiant/rapport/{$id}/submit-corrections");
        }

        $currentUser = $this->getCurrentUser();
        if (!$currentUser || $currentUser['id_type_utilisateur'] !== 'TYPE_ETUD') {
            $this->setFlashMessage('error', "Accès refusé.");
            $this->redirect("/dashboard/etudiant/rapport/{$id}");
        }
        $numeroCarteEtudiant = $currentUser['numero_utilisateur'];

        // Les sections corrigées viendraient de l'éditeur WYSIWYG
        $sectionsContenuCorriges = [
            'introduction' => $this->getRequestData('section_introduction', ''),
            'corps_rapport' => $this->getRequestData('section_corps_rapport', ''),
            'conclusion' => $this->getRequestData('section_conclusion', ''),
        ];
        $noteExplicative = $this->getRequestData('note_explicative');

        $rules = [
            'note_explicative' => 'nullable|string|max:500',
            // Ajouter des règles de validation pour les sections de contenu si nécessaire
        ];
        $this->validator->validate(['note_explicative' => $noteExplicative], $rules);
        // On peut ajouter des validations pour la taille/contenu de chaque section si besoin.

        if (!$this->validator->isValid()) {
            $this->setFlashMessage('error', implode('<br>', $this->validator->getErrors()));
            $this->redirect("/dashboard/etudiant/rapport/{$id}/submit-corrections");
        }

        try {
            $this->rapportService->enregistrerCorrectionsSoumises($id, $sectionsContenuCorriges, $numeroCarteEtudiant, $noteExplicative);
            $this->setFlashMessage('success', 'Corrections soumises avec succès. Votre rapport sera de nouveau vérifié.');
            $this->redirect("/dashboard/etudiant/rapport/{$id}"); // Rediriger vers le suivi
        } catch (\Exception $e) {
            $this->setFlashMessage('error', 'Erreur lors de la soumission des corrections: ' . $e->getMessage());
            $this->redirect("/dashboard/etudiant/rapport/{$id}/submit-corrections");
        }
    }

    // Les méthodes create(), update(), delete() génériques sont remplacées par des actions spécifiques.
    /*
    public function create(): void {}
    public function update($id): void {}
    public function delete($id): void {}
    */
}