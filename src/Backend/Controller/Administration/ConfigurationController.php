<?php
// src/Backend/Controller/Administration/ConfigurationController.php

namespace App\Backend\Controller\Administration;

use App\Backend\Controller\BaseController;
use App\Backend\Service\Systeme\ServiceSystemeInterface;
use App\Backend\Service\Securite\ServiceSecuriteInterface;
use App\Config\Container;

class ConfigurationController extends BaseController
{
    private ServiceSystemeInterface $serviceSysteme;

    public function __construct(
        Container $container,
        ServiceSecuriteInterface $serviceSecurite,
        ServiceSystemeInterface $serviceSysteme
    ) {
        parent::__construct($container, $serviceSecurite);
        $this->serviceSysteme = $serviceSysteme;
    }

    /**
     * Affiche la page de configuration principale avec ses onglets.
     */
    public function showConfigurationPage(): void
    {
        $this->checkPermission('TRAIT_ADMIN_CONFIG_PAGE_VIEW');
        $this->render('Administration/configuration.php', [
            'title' => 'Configuration du Système',
            'activeTab' => $_GET['tab'] ?? 'general'
        ]);
    }

    /**
     * Sauvegarde les paramètres système généraux.
     */
    public function saveSystemParameters(): void
    {
        $this->checkPermission('TRAIT_ADMIN_CONFIG_PARAMS_EDIT');
        // ... Logique de validation et appel à $this->serviceSysteme->setParametres() ...
        $this->jsonResponse(['success' => true, 'message' => 'Paramètres sauvegardés.']);
    }

    /**
     * API pour lister les entrées d'un référentiel.
     */
    public function listReferentielEntries(string $name): void
    {
        $this->checkPermission('TRAIT_ADMIN_CONFIG_REFERENTIELS_VIEW');
        $data = $this->serviceSysteme->gererReferentiel('list', $name);
        $this->jsonResponse(['success' => true, 'data' => $data]);
    }

    /**
     * API pour sauvegarder une entrée de référentiel.
     */
    public function saveReferentielEntry(string $name): void
    {
        $this->checkPermission('TRAIT_ADMIN_CONFIG_REFERENTIELS_EDIT');
        $id = $_POST['id'] ?? null;
        $data = $_POST['data'];
        $this->serviceSysteme->gererReferentiel($id ? 'update' : 'create', $name, $id, $data);
        $this->jsonResponse(['success' => true]);
    }

    /**
     * API pour supprimer une entrée de référentiel.
     */
    public function deleteReferentielEntry(string $name, string $id): void
    {
        $this->checkPermission('TRAIT_ADMIN_CONFIG_REFERENTIELS_EDIT');
        $this->serviceSysteme->gererReferentiel('delete', $name, $id);
        $this->jsonResponse(['success' => true]);
    }

    /**
     * API pour mettre à jour l'ordre des menus (reçoit un tableau JSON).
     */
    public function updateMenuOrder(): void
    {
        $this->checkPermission('TRAIT_ADMIN_CONFIG_MENUS_EDIT');
        $orderData = json_decode(file_get_contents('php://input'), true);
        // ... Logique pour appeler un service qui met à jour la colonne 'ordre_affichage' ...
        $this->jsonResponse(['success' => true]);
    }
}