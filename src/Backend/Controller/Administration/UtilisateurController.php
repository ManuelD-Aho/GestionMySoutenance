<?php
// src/Backend/Controller/Administration/UtilisateurController.php

namespace App\Backend\Controller\Administration;

use App\Backend\Controller\BaseController;
use App\Config\Container;
use App\Backend\Service\Utilisateur\ServiceUtilisateurInterface;
use App\Backend\Util\FormValidator;

class UtilisateurController extends BaseController
{
    private ServiceUtilisateurInterface $serviceUtilisateur;
    private FormValidator $validator;

    public function __construct(Container $container)
    {
        parent::__construct($container);
        $this->serviceUtilisateur = $container->get(ServiceUtilisateurInterface::class);
        $this->validator = $container->get(FormValidator::class);
    }

    // ... (Le reste du fichier est correct)
}