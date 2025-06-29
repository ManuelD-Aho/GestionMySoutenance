<?php

declare(strict_types=1);

namespace App\Backend\Service;

use PDO;
use App\Backend\Model\CompteRendu;
use App\Backend\Model\ValidationPv;
use App\Backend\Model\Affecter;
use App\Backend\Service\Interface\ProcesVerbalServiceInterface;
use App\Backend\Service\Interface\AuditServiceInterface;
use App\Backend\Service\Interface\DocumentGeneratorServiceInterface;
use App\Backend\Service\Interface\NotificationServiceInterface;
use App\Backend\Exception\ElementNonTrouveException;
use App\Backend\Exception\OperationImpossibleException;
use App\Backend\Exception\PermissionException;

class ServiceProcesVerbal implements ProcesVerbalServiceInterface
{
    private PDO $pdo;
    private CompteRendu $compteRenduModel;
    private ValidationPv $validationPvModel;
    private Affecter $affecterModel;
    private AuditServiceInterface $auditService;
    private DocumentGeneratorServiceInterface $docGenService;
    private NotificationServiceInterface $notificationService;

    public function __construct(
        PDO $pdo,
        CompteRendu $compteRenduModel,
        ValidationPv $validationPvModel,
        Affecter $affecterModel,
        AuditServiceInterface $auditService,
        DocumentGeneratorServiceInterface $docGenService,
        NotificationServiceInterface $notificationService
    ) {
        $this->pdo = $pdo;
        $this->compteRenduModel = $compteRenduModel;
        $this->validationPvModel = $validationPvModel;
        $this->affecterModel = $affecterModel;
        $this->auditService = $auditService;
        $this->docGenService = $docGenService;
        $this->notificationService = $notificationService;
    }

    public function creerCompteRendu(string $idRapport, string $idRedacteur, string $libelle, \DateTimeInterface $dateLimiteApprobation): string
    {
        $donnees = [
            'id_compte_rendu' => 'CR-' . uniqid(), // Utiliser IdentifiantGenerator en production
            'id_rapport_etudiant' => $idRapport,
            'type_pv' => 'Individuel',
            'libelle_compte_rendu' => $libelle,
            'date_creation_pv' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
            'id_statut_pv' => 'PV_BROUILLON', // ID depuis statut_pv_ref
            'id_redacteur' => $idRedacteur,
            'date_limite_approbation' => $dateLimiteApprobation->format('Y-m-d H:i:s'),
        ];
        $this->compteRenduModel->creer($donnees);
        $this->auditService->enregistrerAction($idRedacteur, 'PV_CREATED', $donnees['id_compte_rendu'], 'CompteRendu');
        return $donnees['id_compte_rendu'];
    }

    public function enregistrerApprobation(string $idCompteRendu, string $numeroEnseignant, string $idDecision, ?string $commentaire): bool
    {
        $this->compteRenduModel->trouverParIdentifiant($idCompteRendu) ?: throw new ElementNonTrouveException("Compte-rendu non trouvé.");

        // La validation doit être idempotente: UPDATE si existe, INSERT sinon.
        $this->pdo->beginTransaction();
        try {
            $sql = "INSERT INTO validation_pv (id_compte_rendu, numero_enseignant, id_decision_validation_pv, date_validation, commentaire_validation_pv)
                    VALUES (:id_cr, :num_ens, :id_dec, NOW(), :comment)
                    ON DUPLICATE KEY UPDATE id_decision_validation_pv = VALUES(id_decision_validation_pv), date_validation = NOW(), commentaire_validation_pv = VALUES(commentaire_validation_pv)";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':id_cr' => $idCompteRendu,
                ':num_ens' => $numeroEnseignant,
                ':id_dec' => $idDecision,
                ':comment' => $commentaire
            ]);

            $this->pdo->commit();
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw new OperationImpossibleException("Impossible d'enregistrer l'approbation : " . $e->getMessage());
        }

        $this->auditService->enregistrerAction($numeroEnseignant, 'PV_APPROVAL_REGISTERED', $idCompteRendu, 'CompteRendu', ['decision' => $idDecision]);

        // Tenter de finaliser automatiquement
        try {
            $this->finaliserCompteRendu($idCompteRendu);
        } catch (OperationImpossibleException $e) {
            // C'est normal si tout le monde n'a pas encore voté.
        }

        return true;
    }

    public function finaliserCompteRendu(string $idCompteRendu): bool
    {
        $compteRendu = $this->compteRenduModel->trouverParIdentifiant($idCompteRendu) ?: throw new ElementNonTrouveException("Compte-rendu non trouvé.");

        $membresCommission = $this->affecterModel->trouverParCritere(['id_rapport_etudiant' => $compteRendu['id_rapport_etudiant']]);
        if (empty($membresCommission)) {
            throw new OperationImpossibleException("Aucun membre de commission assigné à ce rapport.");
        }
        $validations = $this->validationPvModel->trouverParCritere(['id_compte_rendu' => $idCompteRendu]);

        $nombreMembres = count($membresCommission);
        $nombreValidations = count($validations);

        if ($nombreValidations < $nombreMembres) {
            throw new OperationImpossibleException("Finalisation impossible : tous les membres n'ont pas encore statué.");
        }

        foreach ($validations as $validation) {
            if ($validation['id_decision_validation_pv'] !== 'DEC_APPROUVE') { // ID depuis decision_validation_pv_ref
                throw new OperationImpossibleException("Finalisation impossible : le PV a été rejeté par au moins un membre.");
            }
        }

        $resultat = $this->compteRenduModel->mettreAJourParIdentifiant($idCompteRendu, ['id_statut_pv' => 'PV_FINALIZE']);
        $this->auditService->enregistrerAction('SYSTEM', 'PV_FINALIZED', $idCompteRendu, 'CompteRendu');
        return $resultat;
    }
}