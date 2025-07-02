<?php
// src/Backend/Controller/HomeController.php

namespace App\Backend\Controller;

use App\Backend\Service\Systeme\ServiceSystemeInterface;
use App\Backend\Service\Securite\ServiceSecuriteInterface; // Ajout de la dépendance
use App\Backend\Service\Supervision\ServiceSupervisionInterface; // Ajout de la dépendance
use Exception;

class HomeController extends BaseController
{
    private ServiceSystemeInterface $systemeService;

    public function __construct(
        ServiceSystemeInterface $systemeService,
        ServiceSecuriteInterface $securiteService, // Injecté pour BaseController
        ServiceSupervisionInterface $supervisionService // Injecté pour BaseController
    ) {
        parent::__construct($securiteService, $supervisionService);
        $this->systemeService = $systemeService;
    }

    public function index(): void
    {
        try {
            if ($this->systemeService->estEnMaintenance()) {
                $message = $this->systemeService->getParametre('MAINTENANCE_MODE_MESSAGE', "Le site est actuellement en maintenance. Veuillez réessayer plus tard.");
                $this->renderError(503, $message);
                return;
            }

            if ($this->securiteService->estUtilisateurConnecte()) {
                $this->redirect('/dashboard');
                return;
            }

            $this->render('home/index', ['title' => 'Bienvenue sur GestionMySoutenance'], 'layout/layout_auth');
        } catch (Exception $e) {
            error_log("Erreur HomeController::index: " . $e->getMessage());
            $this->renderError(500, "Une erreur inattendue est survenue.");
        }
    }

    public function about(): void
    {
        $this->render('home/about', ['title' => 'À propos de nous'], 'layout/layout_auth');
    }
}