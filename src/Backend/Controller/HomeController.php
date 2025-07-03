<?php
// src/Backend/Controller/HomeController.php

namespace App\Backend\Controller;

use App\Backend\Service\Systeme\ServiceSystemeInterface;
use App\Backend\Service\Securite\ServiceSecuriteInterface;
use App\Backend\Service\Supervision\ServiceSupervisionInterface;
use App\Backend\Util\FormValidator; // Assurez-vous que cette ligne est présente
use Exception;

class HomeController extends BaseController
{
    private ServiceSystemeInterface $systemeService;

    public function __construct(
        ServiceSystemeInterface $systemeService,
        ServiceSecuriteInterface $securiteService,
        ServiceSupervisionInterface $supervisionService,
        FormValidator $validator // Ajout du FormValidator ici
    ) {
        parent::__construct($securiteService, $supervisionService, $validator);
        $this->systemeService = $systemeService;
    }

    public function index(): void
    {
        try {
            // Vérifie si le mode maintenance est activé
            if ($this->systemeService->estEnMaintenance()) {
                $message = $this->systemeService->getParametre('MAINTENANCE_MODE_MESSAGE', "Le site est actuellement en maintenance. Veuillez réessayer plus tard.");
                $this->renderError(503, $message);
                return; // Termine l'exécution après l'affichage de la page de maintenance
            }

            // Redirige vers le tableau de bord si l'utilisateur est déjà connecté
            if ($this->securiteService->estUtilisateurConnecte()) {
                $this->redirect('/dashboard');
                return; // Termine l'exécution après la redirection
            }

            // Affiche la page d'accueil pour les utilisateurs non connectés
            $this->render('home/index', ['title' => 'Bienvenue sur GestionMySoutenance'], 'layout/layout_auth');

        } catch (Exception $e) {
            // Log l'erreur pour le débogage
            error_log("Erreur HomeController::index: " . $e->getMessage());
            // Affiche une page d'erreur générique à l'utilisateur
            $this->renderError(500, "Une erreur inattendue est survenue lors du chargement de la page d'accueil.");
        }
    }

    public function about(): void
    {
        // Cette méthode n'a pas de logique de redirection ou d'erreur spécifique dans les logs fournis,
        // donc elle reste simple.
        $this->render('home/about', ['title' => 'À propos de nous'], 'layout/layout_auth');
    }
}