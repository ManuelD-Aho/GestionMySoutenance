<?php

namespace App\Backend\Controller;

class HomeController extends BaseController
{
    public function home(): void
    {
        // Cette méthode est héritée de BaseController, mais ici tu peux la personnaliser
        if ($this->authService && $this->authService->estUtilisateurConnecteEtSessionValide()) {
            $this->redirect('/dashboard');
        } else {
            $this->redirect('/login');
        }
    }
}

