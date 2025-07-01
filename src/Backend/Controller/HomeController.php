<?php
// src/Backend/Controller/HomeController.php

namespace App\Backend\Controller;

use App\Config\Container;
use App\Backend\Service\Systeme\ServiceSystemeInterface;

class HomeController extends BaseController
{
    private ServiceSystemeInterface $systemeService;

    public function __construct(Container $container)
    {
        parent::__construct($container);
        $this->systemeService = $container->get(ServiceSystemeInterface::class);
    }

    public function index(): void
    {
        if ($this->systemeService->estEnMaintenance()) {
            $message = $this->systemeService->getParametre('MAINTENANCE_MODE_MESSAGE', "Le site est actuellement en maintenance. Veuillez réessayer plus tard.");
            $this->renderError(503, $message);
        }

        if ($this->securiteService->estUtilisateurConnecte()) {
            $this->redirect('/dashboard');
        }

        $this->render('home/index', ['title' => 'Bienvenue sur GestionMySoutenance'], 'layout/layout_auth');
    }

    public function about(): void
    {
        $this->render('home/about', ['title' => 'À propos de nous'], 'layout/layout_auth');
    }
}