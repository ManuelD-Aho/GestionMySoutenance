<?php

declare(strict_types=1);

namespace App\Backend\Service;

use PDO;
use App\Backend\Model\Penalite;
use App\Backend\Model\RapportEtudiant;
use App\Backend\Service\Interface\PenaliteServiceInterface;
use App\Backend\Service\Interface\AuditServiceInterface;
use App\Backend\Service\Interface\NotificationServiceInterface;
use App\Backend\Service\Interface\IdentifiantGeneratorInterface;
use App\Backend\Exception\ElementNonTrouveException;
use App\Backend\Exception\OperationImpossibleException;

class ServicePenalite implements PenaliteServiceInterface
{
    private PDO $pdo;
    private Penalite $penaliteModel;
    private RapportEtudiant $rapportEtudiantModel;
    private AuditServiceInterface $auditService;
    private NotificationServiceInterface $notificationService;
    private IdentifiantGeneratorInterface $identifiantGenerator;
    private string $currentUserLogin;

    public function __construct(
        PDO $pdo,
        Penalite $penaliteModel,
        RapportEtudiant $rapportEtudiantModel,
        AuditServiceInterface $auditService,
        NotificationServiceInterface $notificationService,
        IdentifiantGeneratorInterface $identifiantGenerator
    ) {
        $this->pdo = $pdo;
        $this->penaliteModel = $penaliteModel;
        $this->rapportEtudiantModel = $rapportEtudiantModel;
        $this->auditService = $auditService;
        $this->notificationService = $notificationService;
        $this->identifiantGenerator = $identifiantGenerator;
        $this->currentUserLogin = $_SESSION['user_id'] ?? 'SYSTEM_SCRIPT';
    }

    public function detecterEtCreerPenalites(): int
    {
        // Cette logique est un exemple et pourrait être affinée.
        // La colonne date_limite_soumission n'existe pas sur rapport_etudiant.
        // On se base sur un placeholder logique.
        $rapportsEnRetard = []; // Placeholder. La logique DDL ne permet pas cette détection.

        $count = 0;
        foreach ($rapportsEnRetard as $rapport) {
            try {
                $this->appliquerPenaliteManuellement(
                    $rapport['numero_carte_etudiant'],
                    "Retard de soumission du rapport '{$rapport['libelle_rapport_etudiant']}'",
                    50.00 // Le montant pourrait venir des paramètres système.
                );
                $this->rapportEtudiantModel->mettreAJourParIdentifiant($rapport['id_rapport_etudiant'], ['id_statut_rapport' => 'RAP_EN_RETARD']);
                $count++;
            } catch (\Exception $e) {
                // Log l'erreur mais continue le traitement.
            }
        }
        return $count;
    }

    public function appliquerPenaliteManuellement(string $numeroEtudiant, string $motif, ?float $montant): string
    {
        $idPenalite = $this->identifiantGenerator->generer('PEN');
        $donnees = [
            'id_penalite' => $idPenalite,
            'numero_carte_etudiant' => $numeroEtudiant,
            'motif' => $motif,
            'montant_du' => $montant,
            'date_creation' => (new \DateTime())->format('Y-m-d H:i:s'),
            'id_statut_penalite' => 'PEN_DUE'
        ];

        $this->penaliteModel->creer($donnees);
        $this->auditService->enregistrerAction($this->currentUserLogin, 'PENALTY_APPLIED', $idPenalite, 'Penalite', $donnees);
        $this->notificationService->envoyerAUtilisateur($numeroEtudiant, 'PENALTY_APPLIED_TPL', ['motif' => $motif, 'montant' => $montant]);

        return $idPenalite;
    }

    public function regulariserPenalite(string $idPenalite, string $idAgent): bool
    {
        $penalite = $this->recupererOuEchouer($idPenalite);
        if ($penalite['id_statut_penalite'] === 'PEN_REGULARISEE') {
            return true;
        }

        $donnees = [
            'id_statut_penalite' => 'PEN_REGULARISEE',
            'date_regularisation' => (new \DateTime())->format('Y-m-d H:i:s'),
            'numero_personnel_traitant' => $idAgent
        ];
        $resultat = $this->penaliteModel->mettreAJourParIdentifiant($idPenalite, $donnees);
        $this->auditService->enregistrerAction($idAgent, 'PENALTY_SETTLED', $idPenalite, 'Penalite');
        return $resultat;
    }

    public function listerPenalites(array $filtres = []): array
    {
        return $this->penaliteModel->trouverParCritere($filtres, ['*'], 'AND', 'date_creation DESC');
    }

    public function getPenalitesPourEtudiant(string $numeroEtudiant): array
    {
        return $this->listerPenalites(['numero_carte_etudiant' => $numeroEtudiant]);
    }

    public function annulerPenalite(string $idPenalite, string $motif): bool
    {
        $penalite = $this->recupererOuEchouer($idPenalite);
        if ($penalite['id_statut_penalite'] === 'PEN_REGULARISEE') {
            throw new OperationImpossibleException("Impossible d'annuler une pénalité déjà réglée.");
        }

        // Le DDL n'a pas de statut 'annulée', on pourrait la supprimer ou la marquer comme réglée avec un commentaire.
        // La suppression est plus propre si le statut n'existe pas.
        $resultat = (bool)$this->penaliteModel->supprimerParIdentifiant($idPenalite);
        if ($resultat) {
            $this->auditService->enregistrerAction($this->currentUserLogin, 'PENALTY_CANCELLED', $idPenalite, 'Penalite', ['motif' => $motif]);
        }
        return $resultat;
    }

    private function recupererOuEchouer(string $idPenalite): array
    {
        $penalite = $this->penaliteModel->trouverParIdentifiant($idPenalite);
        if (!$penalite) {
            throw new ElementNonTrouveException("La pénalité avec l'ID '{$idPenalite}' n'a pas été trouvée.");
        }
        return $penalite;
    }
}