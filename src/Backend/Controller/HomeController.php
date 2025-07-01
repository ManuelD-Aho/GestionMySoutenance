<?php
// src/Backend/Controller/HomeController.php

namespace App\Backend\Controller;

/**
 * Gère la page d'accueil publique de l'application.
 * Son unique rôle est de rediriger l'utilisateur.
 */
class HomeController extends BaseController
{
    /**
     * Redirige vers le tableau de bord si l'utilisateur est déjà connecté,
     * sinon, redirige vers la page de connexion.
     */
    public function index(): void
    {
        if ($this->serviceSecurite->estUtilisateurConnecte()) {
            $this->redirect('/dashboard');
        } else {
            $this->redirect('/login');
        }
    }
}