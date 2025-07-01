<?php
// src/Backend/Controller/HomeController.php

namespace App\Backend\Controller;

use App\Backend\Service\Securite\ServiceSecuriteInterface;
use App\Backend\Service\Supervision\ServiceSupervisionInterface;
use App\Backend\Util\FormValidator;

class HomeController extends BaseController
{
    public function __construct(
        ServiceSecuriteInterface $serviceSecurite,
        ServiceSupervisionInterface $serviceSupervision,
        FormValidator $formValidator
    ) {
        parent::__construct($serviceSecurite, $serviceSupervision, $formValidator);
    }

    public function index(): void
    {
        if ($this->serviceSecurite->estUtilisateurConnecte()) {
            $this->redirect('/dashboard');
        } else {
            $this->redirect('/login');
        }
    }
}