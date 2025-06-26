<?php
namespace App\Backend\Controller\Admin;

use App\Backend\Controller\BaseController;
use App\Backend\Service\Authentication\ServiceAuthentication;
use App\Backend\Service\Permissions\ServicePermissions;
use App\Backend\Util\FormValidator;
use App\Backend\Service\ConfigurationSysteme\ServiceConfigurationSysteme; // Importer le service
use App\Backend\Exception\ElementNonTrouveException;
use App\Backend\Exception\OperationImpossibleException;
use App\Backend\Exception\DoublonException;

class AnneeAcademiqueController extends BaseController
{
    private ServiceConfigurationSysteme $configService;

    public function __construct(
        ServiceAuthentication       $authService,
        ServicePermissions          $permissionService,
        FormValidator               $validator,
        ServiceConfigurationSysteme $configService // Injection du service
    ) {
        parent::__construct($authService, $permissionService, $validator);
        $this->configService = $configService;
    }

    /**
     * Affiche la liste des années académiques.
     */
    public function index(): void
    {
        $this->requirePermission('TRAIT_ADMIN_CONFIG_ANNEE_ACADEMIQUE_LISTER'); // Exiger la permission

        try {
            $anneesAcademiques = $this->configService->listerAnneesAcademiques();
            $data = [
                'page_title' => 'Gestion des Années Académiques',
                'annees' => $anneesAcademiques
            ];
            $this->render('Administration/ConfigSysteme/annee_academique', $data);
        } catch (\Exception $e) {
            $this->setFlashMessage('error', "Erreur lors du chargement des années académiques: " . $e->getMessage());
            $this->redirect('/dashboard/admin/config'); // Rediriger vers un tableau de bord admin général
        }
    }

    /**
     * Affiche le formulaire de création d'une nouvelle année académique ou la traite.
     */
    public function create(): void
    {
        $this->requirePermission('TRAIT_ADMIN_CONFIG_ANNEE_ACADEMIQUE_CREER'); // Exiger la permission

        if ($this->isPostRequest()) {
            $this->handleCreate();
        } else {
            $data = [
                'page_title' => 'Ajouter une Année Académique',
                'form_action' => '/dashboard/admin/config/annee-academique/create'
            ];
            $this->render('Administration/ConfigSysteme/annee_academique_form', $data); // Créer une vue spécifique pour le formulaire
        }
    }

    /**
     * Traite la soumission du formulaire de création d'une année académique.
     */
    private function handleCreate(): void
    {
        $anneeAcademiqueData = [
            'id_annee_academique' => $this->getRequestData('id_annee_academique'), // ID unique formaté
            'libelle_annee_academique' => $this->getRequestData('libelle_annee_academique'),
            'date_debut' => $this->getRequestData('date_debut'),
            'date_fin' => $this->getRequestData('date_fin'),
            'est_active' => (bool)$this->getRequestData('est_active', false)
        ];

        // Règles de validation
        $rules = [
            'id_annee_academique' => 'required|string|max:50',
            'libelle_annee_academique' => 'required|string|max:50',
            'date_debut' => 'required|date',
            'date_fin' => 'required|date',
            'est_active' => 'boolean'
        ];

        $this->validator->validate($anneeAcademiqueData, $rules);

        if (!$this->validator->isValid()) {
            $this->setFlashMessage('error', implode('<br>', $this->validator->getErrors()));
            $this->redirect('/dashboard/admin/config/annee-academique/create');
        }

        try {
            // Utiliser le service de config pour créer
            $this->configService->creerAnneeAcademique($anneeAcademiqueData['id_annee_academique'], $anneeAcademiqueData['libelle_annee_academique'], $anneeAcademiqueData['date_debut'], $anneeAcademiqueData['date_fin'], $anneeAcademiqueData['est_active']);
            $this->setFlashMessage('success', 'Année académique ajoutée avec succès.');
            $this->redirect('/dashboard/admin/config/annee-academique');
        } catch (DoublonException $e) {
            $this->setFlashMessage('error', 'Erreur: ' . $e->getMessage());
            $this->redirect('/dashboard/admin/config/annee-academique/create');
        } catch (OperationImpossibleException $e) {
            $this->setFlashMessage('error', 'Opération impossible: ' . $e->getMessage());
            $this->redirect('/dashboard/admin/config/annee-academique/create');
        } catch (\Exception $e) {
            $this->setFlashMessage('error', 'Une erreur inattendue est survenue: ' . $e->getMessage());
            $this->redirect('/dashboard/admin/config/annee-academique/create');
        }
    }

    /**
     * Affiche le formulaire de modification d'une année académique ou la traite.
     * @param string $id L'ID de l'année académique à modifier.
     */
    public function edit(string $id): void
    {
        $this->requirePermission('TRAIT_ADMIN_CONFIG_ANNEE_ACADEMIQUE_MODIFIER'); // Exiger la permission

        if ($this->isPostRequest()) {
            $this->handleEdit($id);
        } else {
            try {
                $annee = $this->configService->recupererAnneeAcademiqueParId($id); // Nouvelle méthode dans ServiceConfig
                if (!$annee) {
                    throw new ElementNonTrouveException("Année académique non trouvée.");
                }
                $data = [
                    'page_title' => 'Modifier Année Académique',
                    'annee' => $annee,
                    'form_action' => "/dashboard/admin/config/annee-academique/{$id}/edit"
                ];
                $this->render('Administration/ConfigSysteme/annee_academique_form', $data);
            } catch (ElementNonTrouveException $e) {
                $this->setFlashMessage('error', $e->getMessage());
                $this->redirect('/dashboard/admin/config/annee-academique');
            } catch (\Exception $e) {
                $this->setFlashMessage('error', 'Erreur: ' . $e->getMessage());
                $this->redirect('/dashboard/admin/config/annee-academique');
            }
        }
    }

    /**
     * Traite la soumission du formulaire de modification d'une année académique.
     * @param string $id L'ID de l'année académique.
     */
    private function handleEdit(string $id): void
    {
        $anneeAcademiqueData = [
            'libelle_annee_academique' => $this->getRequestData('libelle_annee_academique'),
            'date_debut' => $this->getRequestData('date_debut'),
            'date_fin' => $this->getRequestData('date_fin'),
            'est_active' => (bool)$this->getRequestData('est_active', false)
        ];

        $rules = [
            'libelle_annee_academique' => 'required|string|max:50',
            'date_debut' => 'required|date',
            'date_fin' => 'required|date',
            'est_active' => 'boolean'
        ];

        $this->validator->validate($anneeAcademiqueData, $rules);

        if (!$this->validator->isValid()) {
            $this->setFlashMessage('error', implode('<br>', $this->validator->getErrors()));
            $this->redirect("/dashboard/admin/config/annee-academique/{$id}/edit");
        }

        try {
            $this->configService->modifierAnneeAcademique($id, $anneeAcademiqueData); // Nouvelle méthode dans ServiceConfig
            // Si l'année est activée, définir comme active via le service
            if ($anneeAcademiqueData['est_active']) {
                $this->configService->definirAnneeAcademiqueActive($id);
            }
            $this->setFlashMessage('success', 'Année académique modifiée avec succès.');
            $this->redirect('/dashboard/admin/config/annee-academique');
        } catch (ElementNonTrouveException $e) {
            $this->setFlashMessage('error', $e->getMessage());
            $this->redirect('/dashboard/admin/config/annee-academique');
        } catch (DoublonException $e) {
            $this->setFlashMessage('error', 'Erreur: ' . $e->getMessage());
            $this->redirect("/dashboard/admin/config/annee-academique/{$id}/edit");
        } catch (OperationImpossibleException $e) {
            $this->setFlashMessage('error', 'Opération impossible: ' . $e->getMessage());
            $this->redirect("/dashboard/admin/config/annee-academique/{$id}/edit");
        } catch (\Exception $e) {
            $this->setFlashMessage('error', 'Une erreur inattendue est survenue: ' . $e->getMessage());
            $this->redirect("/dashboard/admin/config/annee-academique/{$id}/edit");
        }
    }

    /**
     * Supprime une année académique.
     * @param string $id L'ID de l'année académique à supprimer.
     */
    public function delete(string $id): void
    {
        $this->requirePermission('TRAIT_ADMIN_CONFIG_ANNEE_ACADEMIQUE_SUPPRIMER'); // Exiger la permission

        try {
            $this->configService->supprimerAnneeAcademique($id); // Nouvelle méthode dans ServiceConfig
            $this->setFlashMessage('success', 'Année académique supprimée avec succès.');
        } catch (ElementNonTrouveException $e) {
            $this->setFlashMessage('error', $e->getMessage());
        } catch (OperationImpossibleException $e) {
            $this->setFlashMessage('error', 'Impossible de supprimer cette année académique : ' . $e->getMessage());
        } catch (\Exception $e) {
            $this->setFlashMessage('error', 'Une erreur inattendue est survenue: ' . $e->getMessage());
        }
        $this->redirect('/dashboard/admin/config/annee-academique');
    }

    /**
     * Méthode utilitaire pour obtenir les éléments de menu de l'administrateur.
     * (Peut être supprimée si le menu est géré dynamiquement par DashboardController).
     */
    private function getAdminMenuItems(): array
    {
        // Cette méthode est un vestige, le menu doit être géré via ServicePermissions et DashboardController
        // et être dynamique selon les permissions de l'utilisateur.
        // Vous pouvez la supprimer si elle n'est plus utilisée pour la génération réelle du menu.
        return [
            ['label' => 'Tableau de Bord', 'url' => '/dashboard/admin'],
            ['label' => 'Gestion Utilisateurs', 'url' => '/dashboard/admin/utilisateurs'],
            // ...
        ];
    }
}