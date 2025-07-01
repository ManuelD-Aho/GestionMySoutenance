<?php
// src/Backend/Controller/DashboardController.php

namespace App\Backend\Controller;

/**
 * Contrôleur de redirection qui aiguille l'utilisateur connecté
 * vers le tableau de bord correspondant à son rôle.
 */
class DashboardController extends BaseController
{
    /**
     * Redirige l'utilisateur vers son tableau de bord spécifique après la connexion.
     */
    public function index(): void
    {
        if (!$this->serviceSecurite->estUtilisateurConnecte()) {
            $this->redirect('/login');
            return;
        }

        $utilisateur = $this->serviceSecurite->getUtilisateurConnecte();
        $idGroupe = $utilisateur['id_groupe_utilisateur'] ?? null;

        $redirectionMap = [
            'GRP_ADMIN_SYS' => '/admin/dashboard',
            'GRP_ETUDIANT' => '/etudiant/dashboard',
            'GRP_COMMISSION' => '/commission/dashboard',
            'GRP_AGENT_CONFORMITE' => '/personnel/dashboard',
            'GRP_RS' => '/personnel/dashboard',
            'GRP_PERS_ADMIN' => '/personnel/dashboard',
        ];

        if (isset($redirectionMap[$idGroupe])) {
            $this->redirect($redirectionMap[$idGroupe]);
        } else {
            // Gère le cas d'un enseignant simple (GRP_ENSEIGNANT) ou de tout autre rôle
            // sans dashboard assigné. La session est détruite et un message clair est affiché.
            $this->setFlash('error', 'Votre rôle actuel ne vous donne pas accès à un tableau de bord. Veuillez contacter un administrateur.');
            $this->serviceSecurite->logout();
            $this->redirect('/login');
        }
    }
}