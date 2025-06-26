<?php

namespace App\Backend\Service\Conformite;

use PDO;
use App\Backend\Model\RapportEtudiant;
use App\Backend\Model\Approuver;
use App\Backend\Model\StatutConformiteRef;
use App\Backend\Model\PersonnelAdministratif;
use App\Backend\Service\Notification\ServiceNotificationInterface;
use App\Backend\Service\SupervisionAdmin\ServiceSupervisionAdminInterface;
use App\Backend\Exception\ElementNonTrouveException;
use App\Backend\Exception\OperationImpossibleException;
use App\Backend\Exception\DoublonException;

class ServiceConformite implements ServiceConformiteInterface
{
    private RapportEtudiant $rapportEtudiantModel;
    private Approuver $approuverModel;
    private StatutConformiteRef $statutConformiteRefModel;
    private PersonnelAdministratif $personnelAdministratifModel;
    private ServiceNotificationInterface $notificationService;
    private ServiceSupervisionAdminInterface $supervisionService;

    public function __construct(
        PDO $db,
        RapportEtudiant $rapportEtudiantModel,
        Approuver $approuverModel,
        StatutConformiteRef $statutConformiteRefModel,
        PersonnelAdministratif $personnelAdministratifModel,
        ServiceNotificationInterface $notificationService,
        ServiceSupervisionAdminInterface $supervisionService
    ) {
        $this->rapportEtudiantModel = $rapportEtudiantModel;
        $this->approuverModel = $approuverModel;
        $this->statutConformiteRefModel = $statutConformiteRefModel;
        $this->personnelAdministratifModel = $personnelAdministratifModel;
        $this->notificationService = $notificationService;
        $this->supervisionService = $supervisionService;
    }

    public function traiterVerificationConformite(string $idRapportEtudiant, string $numeroPersonnelAdministratif, string $idStatutConformite, ?string $commentaireConformite): bool
    {
        $rapport = $this->rapportEtudiantModel->trouverParIdentifiant($idRapportEtudiant);
        if (!$rapport) {
            throw new ElementNonTrouveException("Rapport étudiant non trouvé.");
        }
        if (!$this->personnelAdministratifModel->trouverParIdentifiant($numeroPersonnelAdministratif)) {
            throw new ElementNonTrouveException("Personnel administratif non trouvé.");
        }
        if (!$this->statutConformiteRefModel->trouverParIdentifiant($idStatutConformite)) {
            throw new ElementNonTrouveException("Statut de conformité non reconnu.");
        }

        if (!in_array($rapport['id_statut_rapport'], ['RAP_SOUMIS', 'RAP_NON_CONF'])) {
            throw new OperationImpossibleException("Le rapport '{$rapport['id_rapport_etudiant']}' n'est pas dans un état permettant la vérification de conformité.");
        }

        if ($idStatutConformite === 'CONF_NOK' && empty($commentaireConformite)) {
            throw new OperationImpossibleException("Un commentaire est obligatoire si le rapport est non conforme.");
        }

        $this->approuverModel->commencerTransaction();
        try {
            $successApprobation = $this->approuverModel->creer([
                'numero_personnel_administratif' => $numeroPersonnelAdministratif,
                'id_rapport_etudiant' => $idRapportEtudiant,
                'id_statut_conformite' => $idStatutConformite,
                'commentaire_conformite' => $commentaireConformite,
                'date_verification_conformite' => date('Y-m-d H:i:s')
            ]);

            if (!$successApprobation) {
                throw new OperationImpossibleException("Échec de l'enregistrement de la vérification de conformité.");
            }

            $newStatutRapport = ($idStatutConformite === 'CONF_OK') ? 'RAP_CONF' : 'RAP_NON_CONF';
            $successRapportUpdate = $this->rapportEtudiantModel->mettreAJourParIdentifiant($idRapportEtudiant, ['id_statut_rapport' => $newStatutRapport]);

            if (!$successRapportUpdate) {
                throw new OperationImpossibleException("Échec de la mise à jour du statut du rapport après vérification.");
            }

            $this->approuverModel->validerTransaction();

            if ($idStatutConformite === 'CONF_OK') {
                $this->notificationService->envoyerNotificationGroupe(
                    'GRP_COMMISSION',
                    'RAPPORT_CONFORME',
                    "Le rapport '{$rapport['libelle_rapport_etudiant']}' (ID: {$idRapportEtudiant}) est maintenant conforme et disponible pour évaluation par la commission."
                );
                $this->notificationService->envoyerNotificationUtilisateur(
                    $rapport['numero_carte_etudiant'],
                    'RAPPORT_CONFORME_ETUDIANT',
                    "Votre rapport '{$rapport['libelle_rapport_etudiant']}' est conforme et a été transmis à la commission."
                );
            } else {
                $this->notificationService->envoyerNotificationUtilisateur(
                    $rapport['numero_carte_etudiant'],
                    'RAPPORT_NON_CONFORME',
                    "Votre rapport '{$rapport['libelle_rapport_etudiant']}' est non conforme. Des corrections sont nécessaires. " . ($commentaireConformite ? "Commentaire: {$commentaireConformite}" : "")
                );
            }

            $this->supervisionService->enregistrerAction(
                $numeroPersonnelAdministratif,
                'VERIF_CONFORMITE_RAPPORT',
                "Rapport '{$idRapportEtudiant}' traité: {$idStatutConformite}",
                $idRapportEtudiant,
                'RapportEtudiant'
            );
            return true;
        } catch (DoublonException $e) {
            $this->approuverModel->annulerTransaction();
            throw new OperationImpossibleException("Ce rapport a déjà été vérifié par ce membre du personnel.");
        } catch (\Exception $e) {
            $this->approuverModel->annulerTransaction();
            throw $e;
        }
    }

    public function recupererRapportsEnAttenteDeVerification(): array
    {
        return $this->rapportEtudiantModel->trouverParCritere([
            'id_statut_rapport' => ['operator' => 'in', 'values' => ['RAP_SOUMIS', 'RAP_NON_CONF']]
        ]);
    }

    public function recupererRapportsTraitesParAgent(string $numeroPersonnelAdministratif): array
    {
        $approbations = $this->approuverModel->trouverParCritere(['numero_personnel_administratif' => $numeroPersonnelAdministratif], ['id_rapport_etudiant']);
        $idsRapports = array_column($approbations, 'id_rapport_etudiant');

        if (empty($idsRapports)) {
            return [];
        }

        return $this->rapportEtudiantModel->trouverParCritere([
            'id_rapport_etudiant' => ['operator' => 'in', 'values' => $idsRapports]
        ]);
    }

    public function getVerificationByAgentAndRapport(string $numeroPersonnelAdministratif, string $idRapportEtudiant): ?array
    {
        return $this->approuverModel->trouverApprobationParCles($numeroPersonnelAdministratif, $idRapportEtudiant);
    }
}