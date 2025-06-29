<?php

declare(strict_types=1);

namespace App\Backend\Controller\Administration;

use App\Config\Container;
use App\Backend\Controller\BaseController;
use App\Backend\Service\Interface\ParametrageServiceInterface;
use App\Backend\Service\Interface\NotificationConfigurationServiceInterface;
use App\Backend\Util\FormValidator;
use App\Backend\Exception\OperationImpossibleException;
use App\Backend\Exception\DoublonException;

class ConfigSystemeController extends BaseController
{
    private ParametrageServiceInterface $parametreService;
    private NotificationConfigurationServiceInterface $notifConfigService;
    private FormValidator $validator;

    public function __construct(Container $container)
    {
        parent::__construct($container);
        $this->parametreService = $container->get(ParametrageServiceInterface::class);
        $this->notifConfigService = $container->get(NotificationConfigurationServiceInterface::class);
        $this->validator = $container->get(FormValidator::class);
    }

    public function index(): void
    {
        $this->checkPermission('TRAIT_ADMIN_CONFIG_ACCEDER');

        try {
            $parametres = $this->parametreService->getAllParametres();
            $this->render('Administration/ConfigSysteme/parametres_generaux', [
                'page_title' => 'Configuration du Système',
                'parametres' => $parametres,
                'csrf_token' => $this->generateCsrfToken()
            ]);
        } catch (\Exception $e) {
            $this->addFlashMessage('error', "Erreur lors du chargement de la configuration: " . $e->getMessage());
            $this->redirect('/dashboard/admin');
        }
    }

    public function updateGeneralParameters(): void
    {
        $this->checkPermission('TRAIT_ADMIN_CONFIG_PARAM_MAJ');

        if (!$this->validateCsrfToken($_POST['csrf_token'] ?? '')) {
            $this->addFlashMessage('error', 'Jeton de sécurité invalide.');
            $this->redirect('/dashboard/admin/config');
            return;
        }

        $parametresData = $_POST['parametres'] ?? [];

        try {
            foreach ($parametresData as $cle => $valeur) {
                $this->parametreService->setParametre($cle, $valeur);
            }
            $this->addFlashMessage('success', 'Paramètres généraux mis à jour avec succès.');
        } catch (OperationImpossibleException $e) {
            $this->addFlashMessage('error', 'Opération impossible: ' . $e->getMessage());
        } catch (\Exception $e) {
            $this->addFlashMessage('error', 'Une erreur inattendue est survenue: ' . $e->getMessage());
        }
        $this->redirect('/dashboard/admin/config');
    }

    public function showDocumentTemplates(): void
    {
        $this->checkPermission('TRAIT_ADMIN_CONFIG_MODELES_DOC_LISTER');
        // Implémentation future
        $this->redirect('/dashboard/admin/config');
    }

    public function handleDocumentTemplate(string $id = null): void
    {
        // Implémentation future
        $this->redirect('/dashboard/admin/config');
    }

    public function deleteDocumentTemplate(string $id): void
    {
        // Implémentation future
        $this->redirect('/dashboard/admin/config');
    }
}