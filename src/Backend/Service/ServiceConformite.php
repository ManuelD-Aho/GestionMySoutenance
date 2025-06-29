<?php

declare(strict_types=1);

namespace App\Backend\Service;

use PDO;
use App\Backend\Model\RapportEtudiant;
use App\Backend\Model\ConformiteRapportDetails;
use App\Backend\Service\Interface\ConformiteServiceInterface;
use App\Backend\Service\Interface\AuditServiceInterface;
use App\Backend\Service\Interface\NotificationServiceInterface;
use App\Backend\Exception\ElementNonTrouveException;
use App\Backend\Exception\OperationImpossibleException;

class ServiceConformite implements ConformiteServiceInterface
{
    private PDO $pdo;
    private RapportEtudiant $rapportEtudiantModel;
    private ConformiteRapportDetails $conformiteDetailsModel;
    private AuditServiceInterface $auditService;
    private NotificationServiceInterface $notificationService;

    public function __construct(
        PDO $pdo,
        RapportEtudiant $rapportEtudiantModel,
        ConformiteRapportDetails $conformiteDetailsModel,
        AuditServiceInterface $auditService,
        NotificationServiceInterface $notificationService
    ) {
        $this->pdo = $pdo;
        $this->rapportEtudiantModel = $rapportEtudiantModel;
        $this->conformiteDetailsModel = $conformiteDetailsModel;
        $this->auditService = $auditService;
        $this->notificationService = $notificationService;
    }

    public function soumettreVerdictConformite(string $idRapport, string $idAgent, string $idStatut, ?string $commentaire): bool
    {
        $rapport = $this->recupererRapportOuEchouer($idRapport);
        if ($rapport['id_statut_rapport'] !== 'RAP_SOUMIS') {
            throw new OperationImpossibleException("Le verdict ne peut être soumis que pour un rapport à l'état 'Soumis'.");
        }

        $nouveauStatutRapport = ($idStatut === 'CONF_ACCEPTE') ? 'RAP_CONFORME' : 'RAP_NON_CONFORME';

        $this->pdo->beginTransaction();
        try {
            $this->rapportEtudiantModel->mettreAJourParIdentifiant($idRapport, [
                'id_statut_conformite' => $idStatut,
                'id_agent_conformite' => $idAgent,
                'date_verif_conformite' => (new \DateTime())->format('Y-m-d H:i:s'),
                'commentaire_conformite' => $commentaire,
                'id_statut_rapport' => $nouveauStatutRapport
            ]);

            $this->auditService->enregistrerAction($idAgent, 'COMPLIANCE_VERDICT_SUBMITTED', $idRapport, 'RapportEtudiant', ['verdict' => $idStatut]);

            if ($nouveauStatutRapport === 'RAP_CONFORME') {
                $this->notificationService->envoyerAUtilisateur($rapport['numero_carte_etudiant'], 'REPORT_COMPLIANT_TPL', ['report_title' => $rapport['titre']]);
                $this->transmettreRapportACommission($idRapport);
            } else {
                $this->notificationService->envoyerAUtilisateur($rapport['numero_carte_etudiant'], 'REPORT_NON_COMPLIANT_TPL', ['report_title' => $rapport['titre'], 'comment' => $commentaire]);
            }

            $this->pdo->commit();
            return true;
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function enregistrerDetailsChecklist(string $idRapport, array $detailsCriteres): bool
    {
        $this->recupererRapportOuEchouer($idRapport);
        $this->pdo->beginTransaction();
        try {
            foreach ($detailsCriteres as $idCritere => $statut) {
                $this->conformiteDetailsModel->creer([
                    'id_rapport_etudiant' => $idRapport,
                    'id_critere' => $idCritere,
                    'est_conforme' => $statut
                ]);
            }
            $this->auditService->enregistrerAction('SYSTEM', 'COMPLIANCE_CHECKLIST_SAVED', $idRapport, 'RapportEtudiant');
            $this->pdo->commit();
            return true;
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function listerRapportsAExaminer(): array
    {
        return $this->rapportEtudiantModel->trouverParCritere(['id_statut_rapport' => 'RAP_SOUMIS']);
    }

    public function listerRapportsTraitesParAgent(string $idAgent): array
    {
        return $this->rapportEtudiantModel->trouverParCritere(['id_agent_conformite' => $idAgent]);
    }

    public function getDetailsConformiteRapport(string $idRapport): ?array
    {
        return $this->conformiteDetailsModel->trouverParCritere(['id_rapport_etudiant' => $idRapport]);
    }

    public function transmettreRapportACommission(string $idRapport): bool
    {
        $rapport = $this->recupererRapportOuEchouer($idRapport);
        if ($rapport['id_statut_rapport'] !== 'RAP_CONFORME') {
            throw new OperationImpossibleException("Seuls les rapports conformes peuvent être transmis à la commission.");
        }

        $this->notificationService->envoyerAGroupe('GRP_COMMISSION', 'REPORT_FORWARDED_TO_COMMISSION_TPL', ['report_title' => $rapport['titre']]);
        $this->auditService->enregistrerAction('SYSTEM', 'REPORT_FORWARDED_TO_COMMISSION', $idRapport, 'RapportEtudiant');

        return true;
    }

    private function recupererRapportOuEchouer(string $idRapport): array
    {
        $rapport = $this->rapportEtudiantModel->trouverParIdentifiant($idRapport);
        if (!$rapport) {
            throw new ElementNonTrouveException("Le rapport avec l'ID '{$idRapport}' n'a pas été trouvé.");
        }
        return $rapport;
    }
}