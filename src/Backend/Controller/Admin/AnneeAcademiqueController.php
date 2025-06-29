<?php

declare(strict_types=1);

namespace App\Backend\Controller\Admin;

use App\Config\Container;
use App\Backend\Controller\BaseController;
use App\Backend\Service\Interface\AnneeAcademiqueServiceInterface;
use App\Backend\Service\Interface\PermissionsServiceInterface;
use App\Backend\Util\FormValidator;
use App\Backend\Exception\ElementNonTrouveException;
use App\Backend\Exception\OperationImpossibleException;
use App\Backend\Exception\DoublonException;

class AnneeAcademiqueController extends BaseController
{
    private AnneeAcademiqueServiceInterface $anneeAcademiqueService;
    private PermissionsServiceInterface $permissionsService;
    private FormValidator $validator;

    public function __construct(Container $container)
    {
        parent::__construct($container);
        $this->anneeAcademiqueService = $container->get(AnneeAcademiqueServiceInterface::class);
        $this->permissionsService = $container->get(PermissionsServiceInterface::class);
        $this->validator = $container->get(FormValidator::class);
    }

    public function index(): void
    {
        $this->checkPermission('TRAIT_ADMIN_CONFIG_ANNEE_ACADEMIQUE_LISTER');

        try {
            $anneesAcademiques = $this->anneeAcademiqueService->listerAnneesAcademiques();
            $this->render('Administration/ConfigSysteme/annee_academique', [
                'page_title' => 'Gestion des Années Académiques',
                'annees' => $anneesAcademiques,
                'csrf_token' => $this->generateCsrfToken()
            ]);
        } catch (\Exception $e) {
            $this->addFlashMessage('error', "Erreur lors du chargement des années académiques: " . $e->getMessage());
            $this->redirect('/dashboard/admin/config');
        }
    }

    public function create(): void
    {
        $this->checkPermission('TRAIT_ADMIN_CONFIG_ANNEE_ACADEMIQUE_CREER');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleCreate();
        } else {
            $this->render('Administration/ConfigSysteme/annee_academique_form', [
                'page_title' => 'Ajouter une Année Académique',
                'form_action' => '/dashboard/admin/config/annee-academique/create',
                'csrf_token' => $this->generateCsrfToken()
            ]);
        }
    }

    private function handleCreate(): void
    {
        if (!$this->validateCsrfToken($_POST['csrf_token'] ?? '')) {
            $this->addFlashMessage('error', 'Jeton de sécurité invalide.');
            $this->redirect('/dashboard/admin/config/annee-academique');
            return;
        }

        $anneeAcademiqueData = [
            'libelle' => $_POST['libelle_annee_academique'] ?? null,
            'date_debut' => $_POST['date_debut'] ?? null,
            'date_fin' => $_POST['date_fin'] ?? null,
            'est_active' => isset($_POST['est_active'])
        ];

        $this->validator->validate($anneeAcademiqueData, [
            'libelle' => 'required|string|max:50',
            'date_debut' => 'required|date',
            'date_fin' => 'required|date'
        ]);

        if (!$this->validator->isValid()) {
            $this->addFlashMessage('error', implode('<br>', $this->validator->getErrors()['libelle'] ?? $this->validator->getErrors()['date_debut'] ?? $this->validator->getErrors()['date_fin']));
            $this->redirect('/dashboard/admin/config/annee-academique/create');
        }

        try {
            $this->anneeAcademiqueService->creerAnneeAcademique($anneeAcademiqueData);
            $this->addFlashMessage('success', 'Année académique ajoutée avec succès.');
            $this->redirect('/dashboard/admin/config/annee-academique');
        } catch (DoublonException | OperationImpossibleException $e) {
            $this->addFlashMessage('error', $e->getMessage());
            $this->redirect('/dashboard/admin/config/annee-academique/create');
        } catch (\Exception $e) {
            $this->addFlashMessage('error', 'Une erreur inattendue est survenue.');
            $this->redirect('/dashboard/admin/config/annee-academique/create');
        }
    }

    public function edit(string $id): void
    {
        $this->checkPermission('TRAIT_ADMIN_CONFIG_ANNEE_ACADEMIQUE_MODIFIER');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleEdit($id);
        } else {
            try {
                $annee = $this->anneeAcademiqueService->recupererAnneeAcademiqueParId($id);
                if (!$annee) {
                    throw new ElementNonTrouveException("Année académique non trouvée.");
                }
                $this->render('Administration/ConfigSysteme/annee_academique_form', [
                    'page_title' => 'Modifier Année Académique',
                    'annee' => $annee,
                    'form_action' => "/dashboard/admin/config/annee-academique/{$id}/edit",
                    'csrf_token' => $this->generateCsrfToken()
                ]);
            } catch (ElementNonTrouveException $e) {
                $this->addFlashMessage('error', $e->getMessage());
                $this->redirect('/dashboard/admin/config/annee-academique');
            }
        }
    }

    private function handleEdit(string $id): void
    {
        if (!$this->validateCsrfToken($_POST['csrf_token'] ?? '')) {
            $this->addFlashMessage('error', 'Jeton de sécurité invalide.');
            $this->redirect('/dashboard/admin/config/annee-academique');
            return;
        }

        $anneeAcademiqueData = [
            'libelle' => $_POST['libelle_annee_academique'] ?? null,
            'date_debut' => $_POST['date_debut'] ?? null,
            'date_fin' => $_POST['date_fin'] ?? null,
            'est_active' => isset($_POST['est_active'])
        ];

        $this->validator->validate($anneeAcademiqueData, [
            'libelle' => 'required|string|max:50',
            'date_debut' => 'required|date',
            'date_fin' => 'required|date'
        ]);

        if (!$this->validator->isValid()) {
            $this->addFlashMessage('error', implode('<br>', $this->validator->getErrors()['libelle'] ?? $this->validator->getErrors()['date_debut'] ?? $this->validator->getErrors()['date_fin']));
            $this->redirect("/dashboard/admin/config/annee-academique/{$id}/edit");
        }

        try {
            $this->anneeAcademiqueService->mettreAJourAnneeAcademique($id, $anneeAcademiqueData);
            if ($anneeAcademiqueData['est_active']) {
                $this->anneeAcademiqueService->definirAnneeAcademiqueActive($id);
            }
            $this->addFlashMessage('success', 'Année académique modifiée avec succès.');
            $this->redirect('/dashboard/admin/config/annee-academique');
        } catch (ElementNonTrouveException | DoublonException | OperationImpossibleException $e) {
            $this->addFlashMessage('error', $e->getMessage());
            $this->redirect("/dashboard/admin/config/annee-academique/{$id}/edit");
        }
    }

    public function delete(string $id): void
    {
        $this->checkPermission('TRAIT_ADMIN_CONFIG_ANNEE_ACADEMIQUE_SUPPRIMER');

        if (!$this->validateCsrfToken($_POST['csrf_token'] ?? '')) {
            $this->addFlashMessage('error', 'Jeton de sécurité invalide.');
            $this->redirect('/dashboard/admin/config/annee-academique');
            return;
        }

        try {
            $this->anneeAcademiqueService->supprimerAnneeAcademique($id);
            $this->addFlashMessage('success', 'Année académique supprimée avec succès.');
        } catch (ElementNonTrouveException | OperationImpossibleException $e) {
            $this->addFlashMessage('error', $e->getMessage());
        }
        $this->redirect('/dashboard/admin/config/annee-academique');
    }

    public function setActive(string $id): void
    {
        $this->checkPermission('TRAIT_ADMIN_CONFIG_ANNEE_ACADEMIQUE_MODIFIER');

        if (!$this->validateCsrfToken($_POST['csrf_token'] ?? '')) {
            $this->addFlashMessage('error', 'Jeton de sécurité invalide.');
            $this->redirect('/dashboard/admin/config/annee-academique');
            return;
        }

        try {
            $this->anneeAcademiqueService->definirAnneeAcademiqueActive($id);
            $this->addFlashMessage('success', 'Année académique définie comme active.');
        } catch (ElementNonTrouveException $e) {
            $this->addFlashMessage('error', $e->getMessage());
        }
        $this->redirect('/dashboard/admin/config/annee-academique');
    }
}