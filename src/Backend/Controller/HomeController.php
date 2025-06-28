<?php
namespace App\Backend\Controller;

use App\Backend\Service\Authentication\ServiceAuthentication;
use App\Backend\Service\Permissions\ServicePermissions;
use App\Backend\Util\FormValidator;

class HomeController extends BaseController
{
    public function __construct(
        ServiceAuthentication $authService,
        ServicePermissions    $permissionService,
        FormValidator         $validator
    ) {
        parent::__construct($authService, $permissionService, $validator);
    }

    /**
     * Affiche la page d'accueil.
     * Redirige vers le tableau de bord si l'utilisateur est déjà connecté.
     */
    public function home(): void
    {
        // Rediriger vers le tableau de bord si l'utilisateur est déjà connecté
        if ($this->authService->estUtilisateurConnecteEtSessionValide()) {
            $this->redirect('/dashboard');
        }

        $data = ['page_title' => 'Bienvenue sur GestionMySoutenance'];
        $this->render('Auth/auth', $data, 'none'); // CHANGEMENT ICI
    }
}