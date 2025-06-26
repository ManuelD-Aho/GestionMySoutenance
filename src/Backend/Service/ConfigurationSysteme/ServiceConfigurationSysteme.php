<?php

namespace App\Backend\Service\ConfigurationSysteme;

use PDO;
use App\Backend\Model\ParametreSysteme;
use App\Backend\Model\TypeDocumentRef;
use App\Backend\Model\NiveauEtude;
use App\Backend\Model\StatutPaiementRef;
use App\Backend\Model\DecisionPassageRef;
use App\Backend\Model\Ecue;
use App\Backend\Model\Grade;
use App\Backend\Model\Fonction;
use App\Backend\Model\Specialite;
use App\Backend\Model\StatutReclamationRef;
use App\Backend\Model\StatutConformiteRef;
use App\Backend\Model\Ue;
use App\Backend\Model\Notification;
use App\Backend\Service\SupervisionAdmin\ServiceSupervisionAdminInterface;
use App\Backend\Service\IdentifiantGenerator\IdentifiantGeneratorInterface;
use App\Backend\Exception\ElementNonTrouveException;
use App\Backend\Exception\OperationImpossibleException;
use App\Backend\Exception\DoublonException;

class ServiceConfigurationSysteme implements ServiceConfigurationSystemeInterface
{
    private ParametreSysteme $parametreSystemeModel;
    private TypeDocumentRef $typeDocumentRefModel;
    private NiveauEtude $niveauEtudeModel;
    private StatutPaiementRef $statutPaiementRefModel;
    private DecisionPassageRef $decisionPassageRefModel;
    private Ecue $ecueModel;
    private Grade $gradeModel;
    private Fonction $fonctionModel;
    private Specialite $specialiteModel;
    private StatutReclamationRef $statutReclamationRefModel;
    private StatutConformiteRef $statutConformiteRefModel;
    private Ue $ueModel;
    private Notification $notificationModel;
    private ServiceSupervisionAdminInterface $supervisionService;
    private IdentifiantGeneratorInterface $idGenerator;

    public function __construct(
        PDO $db,
        ParametreSysteme $parametreSystemeModel,
        TypeDocumentRef $typeDocumentRefModel,
        NiveauEtude $niveauEtudeModel,
        StatutPaiementRef $statutPaiementRefModel,
        DecisionPassageRef $decisionPassageRefModel,
        Ecue $ecueModel,
        Grade $gradeModel,
        Fonction $fonctionModel,
        Specialite $specialiteModel,
        StatutReclamationRef $statutReclamationRefModel,
        StatutConformiteRef $statutConformiteRefModel,
        Ue $ueModel,
        Notification $notificationModel,
        ServiceSupervisionAdminInterface $supervisionService,
        IdentifiantGeneratorInterface $idGenerator
    ) {
        $this->parametreSystemeModel = $parametreSystemeModel;
        $this->typeDocumentRefModel = $typeDocumentRefModel;
        $this->niveauEtudeModel = $niveauEtudeModel;
        $this->statutPaiementRefModel = $statutPaiementRefModel;
        $this->decisionPassageRefModel = $decisionPassageRefModel;
        $this->ecueModel = $ecueModel;
        $this->gradeModel = $gradeModel;
        $this->fonctionModel = $fonctionModel;
        $this->specialiteModel = $specialiteModel;
        $this->statutReclamationRefModel = $statutReclamationRefModel;
        $this->statutConformiteRefModel = $statutConformiteRefModel;
        $this->ueModel = $ueModel;
        $this->notificationModel = $notificationModel;
        $this->supervisionService = $supervisionService;
        $this->idGenerator = $idGenerator;
    }

    public function mettreAJourParametresGeneraux(array $parametres): bool
    {
        $this->parametreSystemeModel->commencerTransaction();
        try {
            foreach ($parametres as $cle => $valeur) {
                $this->parametreSystemeModel->mettreAJourParIdentifiant($cle, ['valeur' => $valeur]);
            }
            $this->parametreSystemeModel->validerTransaction();
            $this->supervisionService->enregistrerAction(
                $_SESSION['user_id'] ?? 'SYSTEM',
                'MISE_AJOUR_PARAM_SYSTEME',
                "Paramètres système mis à jour"
            );
            return true;
        } catch (\Exception $e) {
            $this->parametreSystemeModel->annulerTransaction();
            throw $e;
        }
    }

    public function recupererParametresGeneraux(): array
    {
        $params = $this->parametreSystemeModel->trouverTout();
        return array_column($params, 'valeur', 'cle');
    }

    public function gererModeleNotificationEmail(?string $idNotification, array $donnees): string
    {
        $this->notificationModel->commencerTransaction();
        try {
            if ($idNotification) {
                if (!$this->notificationModel->trouverParIdentifiant($idNotification)) {
                    throw new ElementNonTrouveException("Modèle de notification non trouvé pour la mise à jour.");
                }
                if (!$this->notificationModel->mettreAJourParIdentifiant($idNotification, $donnees)) {
                    throw new OperationImpossibleException("Échec de la mise à jour du modèle de notification.");
                }
                $actionType = 'MISE_AJOUR_MODELE_NOTIFICATION';
                $actionDetails = "Modèle de notification '{$idNotification}' mis à jour.";
            } else {
                $newIdNotification = $this->idGenerator->genererIdentifiantUnique('NOTIF');
                $donnees['id_notification'] = $newIdNotification;
                if (!$this->notificationModel->creer($donnees)) {
                    throw new OperationImpossibleException("Échec de la création du modèle de notification.");
                }
                $idNotification = $newIdNotification;
                $actionType = 'CREATION_MODELE_NOTIFICATION';
                $actionDetails = "Modèle de notification '{$idNotification}' créé.";
            }

            $this->notificationModel->validerTransaction();
            $this->supervisionService->enregistrerAction(
                $_SESSION['user_id'] ?? 'SYSTEM',
                $actionType,
                $actionDetails,
                $idNotification,
                'Notification'
            );
            return $idNotification;
        } catch (\Exception $e) {
            $this->notificationModel->annulerTransaction();
            throw $e;
        }
    }

    public function listerTypesDocument(): array
    {
        return $this->typeDocumentRefModel->trouverTout();
    }

    public function listerNiveauxEtude(): array
    {
        return $this->niveauEtudeModel->trouverTout();
    }

    public function listerStatutsPaiement(): array
    {
        return $this->statutPaiementRefModel->trouverTout();
    }

    public function listerDecisionsPassage(): array
    {
        return $this->decisionPassageRefModel->trouverTout();
    }

    public function listerEcues(): array
    {
        return $this->ecueModel->trouverTout();
    }

    public function listerGrades(): array
    {
        return $this->gradeModel->trouverTout();
    }

    public function listerFonctions(): array
    {
        return $this->fonctionModel->trouverTout();
    }

    public function listerSpecialites(): array
    {
        return $this->specialiteModel->trouverTout();
    }

    public function listerStatutsReclamation(): array
    {
        return $this->statutReclamationRefModel->trouverTout();
    }

    public function listerStatutsConformite(): array
    {
        return $this->statutConformiteRefModel->trouverTout();
    }

    public function listerUes(): array
    {
        return $this->ueModel->trouverTout();
    }

    public function creerElementReferentiel(string $referentielCode, array $donnees): bool
    {
        $model = $this->getModelForReferentiel($referentielCode);
        return $model->creer($donnees);
    }

    public function modifierElementReferentiel(string $referentielCode, string $id, array $donnees): bool
    {
        $model = $this->getModelForReferentiel($referentielCode);
        return $model->mettreAJourParIdentifiant($id, $donnees);
    }

    public function supprimerElementReferentiel(string $referentielCode, string $id): bool
    {
        $model = $this->getModelForReferentiel($referentielCode);
        return $model->supprimerParIdentifiant($id);
    }

    public function recupererElementReferentiel(string $referentielCode, string $id): ?array
    {
        $model = $this->getModelForReferentiel($referentielCode);
        return $model->trouverParIdentifiant($id);
    }

    public function lierEcueAUe(string $idEcue, string $idUe): bool
    {
        if (!$this->ecueModel->trouverParIdentifiant($idEcue)) {
            throw new ElementNonTrouveException("ECUE non trouvé.");
        }
        if (!$this->ueModel->trouverParIdentifiant($idUe)) {
            throw new ElementNonTrouveException("UE non trouvée.");
        }
        return $this->ecueModel->mettreAJourParIdentifiant($idEcue, ['id_ue' => $idUe]);
    }

    public function listerEcuesParUe(string $idUe): array
    {
        return $this->ecueModel->trouverParCritere(['id_ue' => $idUe]);
    }

    private function getModelForReferentiel(string $referentielCode)
    {
        return match ($referentielCode) {
            'type_document' => $this->typeDocumentRefModel,
            'niveau_etude' => $this->niveauEtudeModel,
            'statut_paiement' => $this->statutPaiementRefModel,
            'decision_passage' => $this->decisionPassageRefModel,
            'ecue' => $this->ecueModel,
            'grade' => $this->gradeModel,
            'fonction' => $this->fonctionModel,
            'specialite' => $this->specialiteModel,
            'statut_reclamation' => $this->statutReclamationRefModel,
            'statut_conformite' => $this->statutConformiteRefModel,
            'ue' => $this->ueModel,
            default => throw new \InvalidArgumentException("Référentiel '{$referentielCode}' non géré."),
        };
    }
}