<?php

declare(strict_types=1);

namespace App\Backend\Controller;

use App\Backend\Service\Interface\AuthenticationServiceInterface;
use App\Backend\Service\Interface\PermissionsServiceInterface;
use App\Backend\Util\FormValidator;

/**
 * HomeController - Le Point d'Entrée Public de l'Application.
 *
 * Rédigé le : 2025-06-29 14:03:31 UTC par ManuelD-Aho
 *
 * Ce contrôleur gère la page d'accueil. Sa principale responsabilité est de présenter
 * la page de connexion aux visiteurs non authentifiés et de rediriger les utilisateurs
 * déjà connectés vers leur tableau de bord approprié.
 */
class HomeController extends BaseController
{
    public function __construct(
        AuthenticationServiceInterface $authService,
        PermissionsServiceInterface $permissionService,
        FormValidator $validator
    ) {
        parent::__construct($authService, $permissionService, $validator);
    }

    /**
     * Affiche la page d'accueil ou redirige vers le tableau de bord.
     * Cette méthode ne nécessite pas de permission, elle est publique.
     * La logique de sécurité est inversée : on agit si l'utilisateur est connecté.
     */
    public function home(): void
    {
        if ($this->authService->estConnecte()) {
            $this->redirect('/dashboard');
        }

        // Pour un visiteur, la page d'accueil EST la page de connexion.
        $this->render('Auth/login');
    }

    /**
     * Surcharge de la méthode execute pour désactiver la vérification de permission
     * pour toutes les actions de ce contrôleur.
     */
    public function execute(string $action, string $permissionRequired, array $vars = []): void
    {
        // Pour HomeController, nous ne vérifions aucune permission.
        // C'est un contrôleur public.
        try {
            call_user_func_array([$this, $action], $vars);
        } catch (\Exception $e) {
            // Logger l'erreur
            $this->render('error/error500', ['message' => 'Une erreur interne est survenue.'], 500);
        }
    }
}