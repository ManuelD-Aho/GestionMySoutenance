<?php

namespace App\Backend\Service\RessourcesEtudiant;

use PDO;
use App\Backend\Service\SupervisionAdmin\ServiceSupervisionAdminInterface;
use App\Backend\Model\CritereConformiteRef;
use App\Backend\Model\ParametreSysteme;

class ServiceRessourcesEtudiant implements ServiceRessourcesEtudiantInterface
{
    private ServiceSupervisionAdminInterface $supervisionService;
    private CritereConformiteRef $critereConformiteRefModel;
    private ParametreSysteme $parametreSystemeModel;

    public function __construct(
        PDO $db,
        ServiceSupervisionAdminInterface $supervisionService,
        CritereConformiteRef $critereConformiteRefModel,
        ParametreSysteme $parametreSystemeModel
    ) {
        $this->supervisionService = $supervisionService;
        $this->critereConformiteRefModel = $critereConformiteRefModel;
        $this->parametreSystemeModel = $parametreSystemeModel;
    }

    public function listerGuidesMethodologiques(): array
    {
        $guides = $this->parametreSystemeModel->trouverParCritere(['cle' => ['operator' => 'LIKE', 'value' => 'GUIDE_%']]);
        $formattedGuides = [];
        foreach ($guides as $guide) {
            $formattedGuides[] = [
                'titre' => $guide['description'],
                'contenu' => $guide['valeur']
            ];
        }
        return $formattedGuides;
    }

    public function listerExemplesRapport(): array
    {
        $exemples = $this->parametreSystemeModel->trouverParCritere(['cle' => ['operator' => 'LIKE', 'value' => 'EXEMPLE_RAPPORT_%']]);
        $formattedExemples = [];
        foreach ($exemples as $exemple) {
            $formattedExemples[] = [
                'titre' => $exemple['description'],
                'url' => $exemple['valeur']
            ];
        }
        return $formattedExemples;
    }

    public function listerCriteresEvaluation(): array
    {
        return $this->critereConformiteRefModel->trouverParCritere(['est_actif' => 1]);
    }

    public function consulterFAQ(): array
    {
        $faqItems = $this->parametreSystemeModel->trouverParCritere(['cle' => ['operator' => 'LIKE', 'value' => 'FAQ_%']]);
        $formattedFaq = [];
        foreach ($faqItems as $item) {
            $formattedFaq[] = [
                'question' => $item['description'],
                'reponse' => $item['valeur']
            ];
        }
        return $formattedFaq;
    }

    public function getCoordonneesSupport(): array
    {
        $supportParams = $this->parametreSystemeModel->trouverParCritere(['cle' => ['operator' => 'LIKE', 'value' => 'SUPPORT_%']]);
        $coordonnees = [];
        foreach ($supportParams as $param) {
            $keyParts = explode('_', strtolower(str_replace('SUPPORT_', '', $param['cle'])));
            if (count($keyParts) === 2) {
                $service = $keyParts[0];
                $contactType = $keyParts[1];
                $coordonnees[$service][$contactType] = $param['valeur'];
            }
        }
        return $coordonnees;
    }
}