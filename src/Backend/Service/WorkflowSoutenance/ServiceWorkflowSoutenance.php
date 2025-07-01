<?php
// src/Backend/Service/WorkflowSoutenance/ServiceWorkflowSoutenance.php

namespace App\Backend\Service\WorkflowSoutenance;

use PDO;
use App\Backend\Model\RapportEtudiant;
use App\Backend\Model\Reclamation;
use App\Backend\Model\GenericModel;
use App\Backend\Service\Communication\ServiceCommunicationInterface;
use App\Backend\Service\Document\ServiceDocumentInterface;
use App\Backend\Service\Supervision\ServiceSupervisionInterface;
use App\Backend\Service\Systeme\ServiceSystemeInterface;
use App\Backend\Exception\{ElementNonTrouveException, OperationImpossibleException, DoublonException, PermissionException};

class ServiceWorkflowSoutenance implements ServiceWorkflowSoutenanceInterface
{
    private PDO $db;
    private RapportEtudiant $rapportModel;
    private Reclamation $reclamationModel;
    private GenericModel $sectionRapportModel;
    private GenericModel $approuverModel;
    private GenericModel $conformiteDetailsModel;
    private GenericModel $affecterModel;
    private GenericModel $voteModel;
    private GenericModel $compteRenduModel;
    private GenericModel $validationPvModel;
    private GenericModel $sessionValidationModel;
    private GenericModel $sessionRapportModel;
    private ServiceCommunicationInterface $communicationService;
    private ServiceDocumentInterface $documentService;
    private ServiceSupervisionInterface $supervisionService;
    private ServiceSystemeInterface $systemeService;

    public function __construct(
        PDO $db,
        RapportEtudiant $rapportModel,
        Reclamation $reclamationModel,
        GenericModel $sectionRapportModel,
        GenericModel $approuverModel,
        GenericModel $conformiteDetailsModel,
        GenericModel $affecterModel,
        GenericModel $voteModel,
        GenericModel $compteRenduModel,
        GenericModel $validationPvModel,
        GenericModel $sessionValidationModel,
        GenericModel $sessionRapportModel,
        ServiceCommunicationInterface $communicationService,
        ServiceDocumentInterface $documentService,
        ServiceSupervisionInterface $supervisionService,
        ServiceSystemeInterface $systemeService
    ) {
        $this->db = $db;
        $this->rapportModel = $rapportModel;
        $this->reclamationModel = $reclamationModel;
        $this->sectionRapportModel = $sectionRapportModel;
        $this->approuverModel = $approuverModel;
        $this->conformiteDetailsModel = $conformiteDetailsModel;
        $this->affecterModel = $affecterModel;
        $this->voteModel = $voteModel;
        $this->compteRenduModel = $compteRenduModel;
        $this->validationPvModel = $validationPvModel;
        $this->sessionValidationModel = $sessionValidationModel;
        $this->sessionRapportModel = $sessionRapportModel;
        $this->communicationService = $communicationService;
        $this->documentService = $documentService;
        $this->supervisionService = $supervisionService;
        $this->systemeService = $systemeService;
    }

    // ====================================================================
    // PHASE 1: GESTION DU RAPPORT PAR L'ÉTUDIANT
    // ====================================================================

    public function creerOuMettreAJourBrouillon(string $numeroEtudiant, array $metadonnees, array $sections): string
    {
        $this->db->beginTransaction();
        try {
            $brouillon = $this->rapportModel->trouverUnParCritere(['numero_carte_etudiant' => $numeroEtudiant, 'id_statut_rapport' => 'RAP_BROUILLON']);

            $metadonnees['date_derniere_modif'] = date('Y-m-d H:i:s');

            if ($brouillon) {
                $idRapport = $brouillon['id_rapport_etudiant'];
                $this->rapportModel->mettreAJourParIdentifiant($idRapport, $metadonnees);
            } else {
                $idRapport = $this->systemeService->genererIdentifiantUnique('RAP');
                $metadonnees['id_rapport_etudiant'] = $idRapport;
                $metadonnees['numero_carte_etudiant'] = $numeroEtudiant;
                $metadonnees['id_statut_rapport'] = 'RAP_BROUILLON';
                $this->rapportModel->creer($metadonnees);
            }

            foreach ($sections as $titre => $contenu) {
                $this->sectionRapportModel->mettreAJourParCles(['id_rapport_etudiant' => $idRapport, 'titre_section' => $titre], ['contenu_section' => $contenu])
                || $this->sectionRapportModel->creer(['id_rapport_etudiant' => $idRapport, 'titre_section' => $titre, 'contenu_section' => $contenu]);
            }

            $this->db->commit();
            return $idRapport;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function soumettreRapport(string $idRapport, string $numeroEtudiant): bool
    {
        $rapport = $this->rapportModel->trouverParIdentifiant($idRapport);
        if (!$rapport || $rapport['numero_carte_etudiant'] !== $numeroEtudiant) throw new PermissionException("Action non autorisée sur ce rapport.");
        if ($rapport['id_statut_rapport'] !== 'RAP_BROUILLON') throw new OperationImpossibleException("Seul un brouillon peut être soumis.");

        $this->supervisionService->enregistrerAction($numeroEtudiant, 'SOUMISSION_RAPPORT', $idRapport, 'RapportEtudiant');
        return $this->changerStatutRapport($idRapport, 'RAP_SOUMIS', 'GRP_AGENT_CONFORMITE', 'NOUVEAU_RAPPORT_A_VERIFIER');
    }

    public function soumettreCorrections(string $idRapport, string $numeroEtudiant, array $sections, string $noteExplicative): bool
    {
        $rapport = $this->rapportModel->trouverParIdentifiant($idRapport);
        if (!$rapport || $rapport['numero_carte_etudiant'] !== $numeroEtudiant) throw new PermissionException("Action non autorisée sur ce rapport.");
        if (!in_array($rapport['id_statut_rapport'], ['RAP_NON_CONF', 'RAP_CORRECT'])) throw new OperationImpossibleException("Ce rapport n'est pas en attente de corrections.");

        $this->creerOuMettreAJourBrouillon($numeroEtudiant, [], $sections);
        $this->supervisionService->enregistrerAction($numeroEtudiant, 'SOUMISSION_CORRECTIONS', $idRapport, 'RapportEtudiant', ['note_explicative' => $noteExplicative]);
        return $this->changerStatutRapport($idRapport, 'RAP_SOUMIS', 'GRP_AGENT_CONFORMITE', 'RAPPORT_CORRIGE_A_VERIFIER');
    }

    public function lireRapportComplet(string $idRapport): ?array
    {
        $rapport = $this->rapportModel->trouverParIdentifiant($idRapport);
        if (!$rapport) return null;
        $rapport['sections'] = $this->sectionRapportModel->trouverParCritere(['id_rapport_etudiant' => $idRapport]);
        $rapport['conformite_details'] = $this->conformiteDetailsModel->trouverParCritere(['id_rapport_etudiant' => $idRapport]);
        $rapport['votes'] = $this->voteModel->trouverParCritere(['id_rapport_etudiant' => $idRapport]);
        return $rapport;
    }

    public function listerRapports(array $filtres = []): array
    {
        return $this->rapportModel->trouverRapportsAvecDetailsEtudiant($filtres);
    }

    // ====================================================================
    // PHASE 2: VÉRIFICATION DE CONFORMITÉ
    // ====================================================================

    public function traiterVerificationConformite(string $idRapport, string $numeroPersonnel, bool $estConforme, array $detailsChecklist, ?string $commentaireGeneral): bool
    {
        $this->db->beginTransaction();
        try {
            $this->approuverModel->creer([
                'numero_personnel_administratif' => $numeroPersonnel,
                'id_rapport_etudiant' => $idRapport,
                'id_statut_conformite' => $estConforme ? 'CONF_OK' : 'CONF_NOK',
                'commentaire_conformite' => $commentaireGeneral,
                'date_verification_conformite' => date('Y-m-d H:i:s')
            ]);

            foreach ($detailsChecklist as $critere) {
                $this->conformiteDetailsModel->creer([
                    'id_conformite_detail' => $this->systemeService->genererIdentifiantUnique('CRD'),
                    'id_rapport_etudiant' => $idRapport,
                    'id_critere' => $critere['id'],
                    'statut_validation' => $critere['statut'],
                    'commentaire' => $critere['commentaire']
                ]);
            }
            $this->db->commit();

            if ($estConforme) {
                return $this->changerStatutRapport($idRapport, 'RAP_CONF', 'GRP_COMMISSION', 'RAPPORT_CONFORME_A_EVALUER');
            } else {
                return $this->changerStatutRapport($idRapport, 'RAP_NON_CONF', null, 'CORRECTIONS_REQUISES');
            }
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    // ====================================================================
    // PHASE 3: GESTION DE LA SESSION DE VALIDATION
    // ====================================================================

    public function creerSession(string $idPresident, array $donneesSession): string
    {
        $idSession = $this->systemeService->genererIdentifiantUnique('SESS');
        $donneesSession['id_session'] = $idSession;
        $donneesSession['id_president_session'] = $idPresident;
        $donneesSession['statut_session'] = 'planifiee';
        $this->sessionValidationModel->creer($donneesSession);
        return $idSession;
    }

    public function modifierSession(string $idSession, string $idPresident, array $donnees): bool { /* ... */ }
    public function composerSession(string $idSession, string $idPresident, array $idsRapports): bool { /* ... */ }
    public function demarrerSession(string $idSession, string $idPresident): bool { /* ... */ }
    public function cloturerSession(string $idSession, string $idPresident): bool { /* ... */ }
    public function listerSessionsPourCommission(): array { /* ... */ }

    // ====================================================================
    // PHASE 4: ÉVALUATION ET VOTE
    // ====================================================================

    public function enregistrerVote(string $idRapport, string $numeroEnseignant, string $decision, ?string $commentaire): bool { /* ... */ }
    public function lancerNouveauTourDeVote(string $idRapport, string $idPresident): bool { /* ... */ }
    public function consulterEtatVotes(string $idSession): array { /* ... */ }
    public function finaliserDecisionCommission(string $idRapport): ?string { /* ... */ }

    // ====================================================================
    // PHASE 5: GESTION DES PROCÈS-VERBAUX (PV)
    // ====================================================================

    public function initierRedactionPv(string $idSession, string $idRedacteur): string { /* ... */ }
    public function mettreAJourContenuPv(string $idCompteRendu, string $idRedacteur, string $contenu): bool { /* ... */ }
    public function soumettrePvPourValidation(string $idCompteRendu, string $idRedacteur): bool { /* ... */ }
    public function approuverOuRejeterPv(string $idCompteRendu, string $numeroMembre, bool $approbation, ?string $commentaire): bool { /* ... */ }
    public function forcerValidationPv(string $idCompteRendu, string $idPresident, string $methode, string $justification): bool { /* ... */ }
    public function genererContenuPvAssiste(string $idSession): array { /* ... */ }

    // ====================================================================
    // PHASE 6: FINALISATION POST-VALIDATION
    // ====================================================================

    public function designerDirecteurMemoire(string $idRapport, string $idPresident, string $numeroEnseignantDirecteur): bool { /* ... */ }

    // --- Méthode privée utilitaire ---
    private function changerStatutRapport(string $idRapport, string $nouveauStatut, ?string $groupeANotifier, ?string $templateNotification): bool
    {
        $rapport = $this->rapportModel->trouverParIdentifiant($idRapport);
        if (!$rapport) throw new ElementNonTrouveException("Rapport non trouvé.");

        $success = $this->rapportModel->mettreAJourParIdentifiant($idRapport, ['id_statut_rapport' => $nouveauStatut]);

        if ($success) {
            $this->supervisionService->enregistrerAction($_SESSION['user_id'] ?? 'SYSTEM', 'CHANGEMENT_STATUT_RAPPORT', $idRapport, 'RapportEtudiant', ['nouveau_statut' => $nouveauStatut]);

            $this->communicationService->envoyerNotificationInterne($rapport['numero_carte_etudiant'], 'STATUT_RAPPORT_MAJ', "Le statut de votre rapport est passé à : {$nouveauStatut}");

            if ($groupeANotifier && $templateNotification) {
                $this->communicationService->envoyerNotificationGroupe($groupeANotifier, $templateNotification, "Le rapport {$idRapport} nécessite votre attention.");
            }
        }
        return $success;
    }
}