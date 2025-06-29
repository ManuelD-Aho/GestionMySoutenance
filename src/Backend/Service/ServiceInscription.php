<?php

declare(strict_types=1);

namespace App\Backend\Service;

use PDO;
use App\Backend\Model\Inscrire;
use App\Backend\Model\Etudiant;
use App\Backend\Model\NiveauEtude;
use App\Backend\Model\Penalite;
use App\Backend\Service\Interface\InscriptionServiceInterface;
use App\Backend\Service\Interface\AuditServiceInterface;
use App\Backend\Service\Interface\NotificationServiceInterface;
use App\Backend\Exception\DoublonException;
use App\Backend\Exception\ElementNonTrouveException;
use App\Backend\Exception\OperationImpossibleException;

class ServiceInscription implements InscriptionServiceInterface
{
    private PDO $pdo;
    private Inscrire $inscrireModel;
    private Etudiant $etudiantModel;
    private NiveauEtude $niveauEtudeModel;
    private Penalite $penaliteModel;
    private AuditServiceInterface $auditService;
    private NotificationServiceInterface $notificationService;

    public function __construct(
        PDO $pdo,
        Inscrire $inscrireModel,
        Etudiant $etudiantModel,
        NiveauEtude $niveauEtudeModel,
        Penalite $penaliteModel,
        AuditServiceInterface $auditService,
        NotificationServiceInterface $notificationService
    ) {
        $this->pdo = $pdo;
        $this->inscrireModel = $inscrireModel;
        $this->etudiantModel = $etudiantModel;
        $this->niveauEtudeModel = $niveauEtudeModel;
        $this->penaliteModel = $penaliteModel;
        $this->auditService = $auditService;
        $this->notificationService = $notificationService;
    }

    public function creerInscription(array $donnees): string
    {
        $numeroEtudiant = $donnees['numero_carte_etudiant'];
        $idNiveauEtude = $donnees['id_niveau_etude'];

        if (!$this->verifierEligibiliteInscription($numeroEtudiant, $idNiveauEtude)) {
            throw new OperationImpossibleException("L'étudiant n'est pas éligible à l'inscription pour ce niveau d'étude.");
        }

        if ($this->inscrireModel->trouverUnParCritere(['numero_carte_etudiant' => $numeroEtudiant, 'id_niveau_etude' => $idNiveauEtude, 'id_annee_academique' => $donnees['id_annee_academique']])) {
            throw new DoublonException("L'étudiant est déjà inscrit à ce niveau pour cette année académique.");
        }

        $this->pdo->beginTransaction();
        try {
            $this->inscrireModel->creer($donnees);
            $idInscription = $this->pdo->lastInsertId();

            $this->auditService->enregistrerAction('SYSTEM_ADMIN', 'INSCRIPTION_CREATED', $idInscription, 'Inscrire', $donnees);
            $this->notificationService->envoyerAUtilisateur($numeroEtudiant, 'INSCRIPTION_SUCCESS_TPL', ['niveau_etude' => $idNiveauEtude]);

            $this->pdo->commit();
            return $idInscription;
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function mettreAJourInscription(string $id, array $donnees): bool
    {
        $inscription = $this->recupererOuEchouer($id);
        $resultat = $this->inscrireModel->mettreAJourParIdentifiant($id, $donnees);
        $this->auditService->enregistrerAction('SYSTEM_ADMIN', 'INSCRIPTION_UPDATED', $id, 'Inscrire', ['anciennes_valeurs' => $inscription, 'nouvelles_valeurs' => $donnees]);
        return $resultat;
    }

    public function supprimerInscription(string $id): bool
    {
        $this->recupererOuEchouer($id);
        $resultat = $this->inscrireModel->supprimerParIdentifiant($id);
        $this->auditService->enregistrerAction('SYSTEM_ADMIN', 'INSCRIPTION_DELETED', $id, 'Inscrire');
        return $resultat;
    }

    public function recupererInscriptionParId(string $id): ?array
    {
        return $this->inscrireModel->trouverParIdentifiant($id);
    }

    public function listerInscriptions(array $filtres = []): array
    {
        return $this->inscrireModel->trouverParCritere($filtres);
    }

    public function validerPaiementInscription(string $id, array $detailsPaiement): bool
    {
        $inscription = $this->recupererOuEchouer($id);
        if ($inscription['id_statut_paiement'] === 'PAIEMENT_VALIDE') {
            return true;
        }

        $donnees = [
            'id_statut_paiement' => 'PAIEMENT_VALIDE',
            'date_paiement' => (new \DateTime())->format('Y-m-d H:i:s'),
            'details_paiement_json' => json_encode($detailsPaiement)
        ];

        $resultat = $this->mettreAJourInscription($id, $donnees);
        if ($resultat) {
            $this->notificationService->envoyerAUtilisateur($inscription['numero_carte_etudiant'], 'PAYMENT_CONFIRMED_TPL', ['id_inscription' => $id]);
        }
        return $resultat;
    }

    public function verifierEligibiliteInscription(string $numeroEtudiant, string $idNiveauEtude): bool
    {
        if (!$this->etudiantModel->trouverParIdentifiant($numeroEtudiant)) {
            throw new ElementNonTrouveException("L'étudiant avec le numéro '{$numeroEtudiant}' n'existe pas.");
        }
        if (!$this->niveauEtudeModel->trouverParIdentifiant($idNiveauEtude)) {
            throw new ElementNonTrouveException("Le niveau d'étude avec l'ID '{$idNiveauEtude}' n'existe pas.");
        }

        $penalitesNonReglees = $this->penaliteModel->compterParCritere([
            'numero_carte_etudiant' => $numeroEtudiant,
            'id_statut_penalite' => 'PEN_DUE'
        ]);

        if ($penalitesNonReglees > 0) {
            throw new OperationImpossibleException("L'étudiant a {$penalitesNonReglees} pénalité(s) non réglée(s) et ne peut pas s'inscrire.");
        }

        return true;
    }

    private function recupererOuEchouer(string $idInscription): array
    {
        $inscription = $this->recupererInscriptionParId($idInscription);
        if (!$inscription) {
            throw new ElementNonTrouveException("L'inscription avec l'ID '{$idInscription}' n'a pas été trouvée.");
        }
        return $inscription;
    }
}