<?php

declare(strict_types=1);

namespace App\Backend\Controller\Administration;

use App\Config\Container;
use App\Backend\Controller\BaseController;
use App\Backend\Service\Interface\ReferentielServiceInterface;
use App\Backend\Util\FormValidator;
use App\Backend\Exception\ElementNonTrouveException;
use App\Backend\Exception\OperationImpossibleException;
use App\Backend\Exception\DoublonException;

class ReferentialController extends BaseController
{
    private ReferentielServiceInterface $referentielService;
    private FormValidator $validator;

    public function __construct(Container $container)
    {
        parent::__construct($container);
        $this->referentielService = $container->get(ReferentielServiceInterface::class);
        $this->validator = $container->get(FormValidator::class);
    }

    public function index(): void
    {
        $this->checkPermission('TRAIT_ADMIN_REFERENTIELS_ACCEDER');
        $this->render('Administration/Referentiels/liste_referentiels', [
            'page_title' => 'Gestion des Référentiels'
        ]);
    }

    public function listItems(string $referentielCode): void
    {
        $this->checkPermission('TRAIT_ADMIN_REFERENTIELS_LISTER');
        // Implémentation future
        $this->redirect('/dashboard/admin/referentiels');
    }

    public function handleItemForm(string $referentielCode, string $id = null): void
    {
        // Implémentation future
        $this->redirect('/dashboard/admin/referentiels');
    }

    public function deleteItem(string $referentielCode, string $id): void
    {
        // Implémentation future
        $this->redirect('/dashboard/admin/referentiels');
    }
}