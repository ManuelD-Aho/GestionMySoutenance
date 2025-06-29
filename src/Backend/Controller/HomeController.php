<?php

declare(strict_types=1);

namespace App\Backend\Controller;

class HomeController extends BaseController
{
    public function home(): void
    {
        if ($this->authService->estConnecte()) {
            $this->redirect('/dashboard');
        }

        $this->render('Auth/auth', [
            'page_title' => 'Authentification',
            'csrf_token' => $this->generateCsrfToken()
        ], 'Auth/layout_auth');
    }
}