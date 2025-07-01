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
use App\Backend\Exception\{ElementNonTrouveException, OperationImpossibleException, PermissionException};

class ServiceWorkflowSoutenance implements ServiceWorkflowSoutenanceInterface
{
    private PDO $db;
    private RapportEtudiant $rapportModel;
    private Reclamation $reclamationModel;
    private GenericModel $sectionRapportModel;
    private GenericModel $approuverModel;
    private GenericModel $conformiteDetailsModel;
    private GenericModel $voteModel;
    private GenericModel $compteRenduModel;
    private GenericModel $sessionValidationModel;
    private GenericModel $sessionRapportModel;
    private GenericModel $affecterModel;
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
        GenericModel $voteModel,
        GenericModel $compteRenduModel,
        GenericModel $sessionValidationModel,
        GenericModel $sessionRapportModel,
        GenericModel $affecterModel,
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
        $this->voteModel = $voteModel;
        $this->compteRenduModel = $compteRenduModel;
        $this->sessionValidationModel = $sessionValidationModel;
        $this->sessionRapportModel = $sessionRapportModel;
        $this->affecterModel = $affecterModel;
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
        if ($rapport['id_statut_rapport'] !== 'RAP_CORRECT') throw new OperationImpossibleException("Ce rapport n'est pas en attente de corrections.");

        // Mise à jour du contenu du rapport
        $this->creerOuMettreAJourBrouillon($numeroEtudiant, [], $sections);
        $this->supervisionService->enregistrerAction($numeroEtudiant, 'SOUMISSION_CORRECTIONS', $idRapport, 'RapportEtudiant', ['note_explicative' => $noteExplicative]);

        // Le rapport est automatiquement validé car il a été revu par le président
        return $this->changerStatutRapport($idRapport, 'RAP_VALID', null, 'RAPPORT_CORRIGE_ET_VALIDE');
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

    public function forcerChangementStatutRapport(string $idRapport, string $nouveauStatut, string $adminId, string $justification): bool
    {
        $rapport = $this->rapportModel->trouverParIdentifiant($idRapport);
        if (!$rapport) throw new ElementNonTrouveException("Rapport non trouvé.");

        $success = $this->rapportModel->mettreAJourParIdentifiant($idRapport, ['id_statut_rapport' => $nouveauStatut]);

        if ($success) {
            $this->supervisionService->enregistrerAction($adminId, 'FORCER_CHANGEMENT_STATUT_RAPPORT', $idRapport, 'RapportEtudiant', ['ancien_statut' => $rapport['id_statut_rapport'], 'nouveau_statut' => $nouveauStatut, 'justification' => $justification]);
            // Notifier l'étudiant du changement de statut forcé
            $this->communicationService->envoyerNotificationInterne($rapport['numero_carte_etudiant'], 'STATUT_RAPPORT_FORCE', "Le statut de votre rapport a été modifié manuellement par l'administration : {$nouveauStatut}. Justification : {$justification}");
        }
        return $success;
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

    public function modifierSession(string $idSession, array $donnees): bool
    {
        $session = $this->sessionValidationModel->trouverParIdentifiant($idSession);
        if (!$session) throw new ElementNonTrouveException("Session non trouvée.");
        if ($session['statut_session'] !== 'planifiee') throw new OperationImpossibleException("Seule une session planifiée peut être modifiée.");

        return $this->sessionValidationModel->mettreAJourParIdentifiant($idSession, $donnees);
    }

    public function composerSession(string $idSession, array $idsRapports): bool
    {
        $this->db->beginTransaction();
        try {
            // D'abord, supprimer les anciennes associations pour cette session
            $this->sessionRapportModel->supprimerParCles(['id_session' => $idSession]);
            // Ensuite, ajouter les nouvelles
            foreach ($idsRapports as $idRapport) {
                $this->sessionRapportModel->creer(['id_session' => $idSession, 'id_rapport_etudiant' => $idRapport]);
            }
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function demarrerSession(string $idSession): bool
    {
        return $this->sessionValidationModel->mettreAJourParIdentifiant($idSession, ['statut_session' => 'en_cours']);
    }

    public function suspendreSession(string $idSession): bool
    {
        return $this->sessionValidationModel->mettreAJourParIdentifiant($idSession, ['statut_session' => 'suspendue']);
    }

    public function reprendreSession(string $idSession): bool
    {
        $session = $this->sessionValidationModel->trouverParIdentifiant($idSession);
        if (!$session) throw new ElementNonTrouveException("Session non trouvée.");
        if ($session['statut_session'] !== 'suspendue') throw new OperationImpossibleException("Seule une session suspendue peut être reprise.");

        return $this->sessionValidationModel->mettreAJourParIdentifiant($idSession, ['statut_session' => 'en_cours']);
    }

    public function cloturerSession(string $idSession): bool
    {
        $rapportsEnCours = $this->sessionRapportModel->trouverParCritere(['id_session' => $idSession, 'statut_rapport' => 'EN_DELIBERATION']);
        if (!empty($rapportsEnCours)) {
            throw new OperationImpossibleException("Impossible de clôturer : des rapports sont encore en délibération.");
        }
        return $this->sessionValidationModel->mettreAJourParIdentifiant($idSession, ['statut_session' => 'cloturee']);
    }

    public function listerSessionsPourCommission(array $filtres = []): array
    {
        return $this->sessionValidationModel->trouverParCritere($filtres, ['*'], 'AND', 'date_creation DESC');
    }

    public function lireSessionComplete(string $idSession): ?array
    {
        $session = $this->sessionValidationModel->trouverParIdentifiant($idSession);
        if (!$session) return null;
        $session['rapports'] = $this->listerRapports(['id_session' => $idSession]); // Suppose une jointure
        return $session;
    }

    public function designerRapporteur(string $idRapport, string $numeroEnseignantRapporteur): bool
    {
        // Cette logique dépend de la structure de votre table 'affecter' ou d'une nouvelle table 'rapporteur_rapport'
        // Pour l'exemple, nous allons supposer que 'affecter' peut être utilisée avec un statut spécifique.
        return (bool) $this->affecterModel->creer([
            'numero_enseignant' => $numeroEnseignantRapporteur,
            'id_rapport_etudiant' => $idRapport,
            'id_statut_jury' => 'JURY_RAPPORTEUR', // Assurez-vous que ce statut existe dans statut_jury
            'directeur_memoire' => 0, // Ce n'est pas un directeur de mémoire
            'date_affectation' => date('Y-m-d H:i:s')
        ]);
    }

    public function recuserMembre(string $idSession, string $numeroEnseignant, string $justification): bool
    {
        // Logique pour marquer un membre comme récusé pour une session spécifique
        // Cela pourrait impliquer une table de liaison session_membre_recuse ou une mise à jour dans affecter
        // Pour l'exemple, nous allons juste enregistrer l'action.
        $this->supervisionService->enregistrerAction(
            $_SESSION['user_id'],
            'RECUSATION_MEMBRE_COMMISSION',
            $idSession,
            'SessionValidation',
            ['membre_recuse' => $numeroEnseignant, 'justification' => $justification]
        );
        return true;
    }

    // ====================================================================
    // PHASE 4: ÉVALUATION ET VOTE
    // ====================================================================

    public function enregistrerVote(string $idRapport, string $idSession, string $numeroEnseignant, string $decision, ?string $commentaire): bool
    {
        $idVote = $this->systemeService->genererIdentifiantUnique('VOTE');
        $tour = $this->voteModel->trouverUnParCritere(['id_rapport_etudiant' => $idRapport], ['MAX(tour_vote) as max_tour'])['max_tour'] ?? 1;

        $success = (bool) $this->voteModel->creer([
            'id_vote' => $idVote,
            'id_session' => $idSession,
            'id_rapport_etudiant' => $idRapport,
            'numero_enseignant' => $numeroEnseignant,
            'id_decision_vote' => $decision,
            'commentaire_vote' => $commentaire,
            'tour_vote' => $tour
        ]);

        if ($success) {
            $this->verifierEtFinaliserVote($idRapport, $idSession);
        }
        return $success;
    }

    public function lancerNouveauTourDeVote(string $idRapport, string $idSession): bool
    {
        $tourActuel = $this->voteModel->trouverUnParCritere(['id_rapport_etudiant' => $idRapport], ['MAX(tour_vote) as max_tour'])['max_tour'] ?? 1;
        $nouveauTour = $tourActuel + 1;

        // Logique pour notifier les membres qu'un nouveau tour a commencé
        $this->supervisionService->enregistrerAction($_SESSION['user_id'], 'NOUVEAU_TOUR_VOTE', $idRapport, 'RapportEtudiant', ['session' => $idSession, 'tour' => $nouveauTour]);
        return true; // L'action est conceptuelle, elle ne modifie pas les anciens votes.
    }

    public function consulterEtatVotes(string $idSession): array
    {
        $sql = "SELECT id_rapport_etudiant, id_decision_vote, COUNT(*) as count FROM vote_commission WHERE id_session = :id_session GROUP BY id_rapport_etudiant, id_decision_vote";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id_session' => $idSession]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ====================================================================
    // PHASE 5: GESTION DES PROCÈS-VERBAUX (PV)
    // ====================================================================

    public function initierRedactionPv(string $idSession, string $idRedacteur): string
    {
        $idPV = $this->systemeService->genererIdentifiantUnique('PV');
        $this->compteRenduModel->creer([
            'id_compte_rendu' => $idPV,
            'type_pv' => 'Session',
            'libelle_compte_rendu' => "PV de la session {$idSession}",
            'id_statut_pv' => 'PV_BROUILLON',
            'id_redacteur' => $idRedacteur
        ]);
        return $idPV;
    }

    public function reassignerRedactionPv(string $idCompteRendu, string $idNouveauRedacteur): bool
    {
        return $this->compteRenduModel->mettreAJourParIdentifiant($idCompteRendu, ['id_redacteur' => $idNouveauRedacteur]);
    }

    public function mettreAJourContenuPv(string $idCompteRendu, string $contenu): bool
    {
        return $this->compteRenduModel->mettreAJourParIdentifiant($idCompteRendu, ['contenu' => $contenu]);
    }

    public function soumettrePvPourApprobation(string $idCompteRendu): bool
    {
        return $this->compteRenduModel->mettreAJourParIdentifiant($idCompteRendu, ['id_statut_pv' => 'PV_ATTENTE_APPROBATION']);
    }

    public function approuverPv(string $idCompteRendu, string $idPresident): bool
    {
        $this->supervisionService->enregistrerAction($idPresident, 'APPROBATION_PV', $idCompteRendu, 'CompteRendu');
        return $this->compteRenduModel->mettreAJourParIdentifiant($idCompteRendu, ['id_statut_pv' => 'PV_VALIDE']);
    }

    public function forcerValidationPv(string $idCompteRendu, string $idPresident, string $justification): bool
    {
        $this->supervisionService->enregistrerAction($idPresident, 'FORCER_VALIDATION_PV', $idCompteRendu, 'CompteRendu', ['justification' => $justification]);
        return $this->compteRenduModel->mettreAJourParIdentifiant($idCompteRendu, ['id_statut_pv' => 'PV_VALIDE']);
    }

    // ====================================================================
    // PHASE 6: FINALISATION POST-VALIDATION
    // ====================================================================

    public function designerDirecteurMemoire(string $idRapport, string $numeroEnseignantDirecteur): bool
    {
        return (bool) $this->affecterModel->creer([
            'numero_enseignant' => $numeroEnseignantDirecteur,
            'id_rapport_etudiant' => $idRapport,
            'id_statut_jury' => 'JURY_DIRECTEUR',
            'directeur_memoire' => 1
        ]);
    }
    // ====================================================================
    // PHASE 7: GESTION DES RÉCLAMATIONS
    // ====================================================================

    public function creerReclamation(string $numeroEtudiant, string $categorie, string $sujet, string $description): string
    {
        $idReclamation = $this->systemeService->genererIdentifiantUnique('RECLA');
        $this->reclamationModel->creer([
            'id_reclamation' => $idReclamation,
            'numero_carte_etudiant' => $numeroEtudiant,
            'categorie_reclamation' => $categorie,
            'sujet_reclamation' => $sujet,
            'description_reclamation' => $description,
            'id_statut_reclamation' => 'RECLA_OUVERTE'
        ]);
        return $idReclamation;
    }

    public function listerReclamations(array $filtres = []): array
    {
        return $this->reclamationModel->trouverParCritere($filtres, ['*'], 'AND', 'date_soumission DESC');
    }

    public function lireReclamation(string $idReclamation): ?array
    {
        return $this->reclamationModel->getDetailsReclamation($idReclamation);
    }

    public function traiterReclamation(string $idReclamation, string $reponse, string $numeroPersonnel): bool
    {
        return $this->reclamationModel->mettreAJourParIdentifiant($idReclamation, [
            'reponse_reclamation' => $reponse,
            'date_reponse' => date('Y-m-d H:i:s'),
            'numero_personnel_traitant' => $numeroPersonnel,
            'id_statut_reclamation' => 'RECLA_RESOLUE'
        ]);
    }

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
    private function verifierEtFinaliserVote(string $idRapport, string $idSession): void
    {
        $session = $this->sessionValidationModel->trouverParIdentifiant($idSession);
        if (!$session) return;

        $nbVotantsRequis = (int) $session['nombre_votants_requis'];
        $votes = $this->voteModel->trouverParCritere(['id_rapport_etudiant' => $idRapport, 'id_session' => $idSession]);

        if (count($votes) < $nbVotantsRequis) return; // Pas assez de votes pour prendre une décision

        $decompte = array_count_values(array_column($votes, 'id_decision_vote'));

        if (isset($decompte['VOTE_REFUSE']) && $decompte['VOTE_REFUSE'] > 0) {
            $this->changerStatutRapport($idRapport, 'RAP_REFUSE', null, 'RAPPORT_REFUSE');
        } elseif (isset($decompte['VOTE_APPROUVE_RESERVE']) && $decompte['VOTE_APPROUVE_RESERVE'] > 0) {
            $this->changerStatutRapport($idRapport, 'RAP_CORRECT', null, 'RAPPORT_CORRECTIONS_REQUISES');
        } elseif (isset($decompte['VOTE_APPROUVE']) && $decompte['VOTE_APPROUVE'] === $nbVotantsRequis) {
            $this->changerStatutRapport($idRapport, 'RAP_VALID', null, 'RAPPORT_VALIDE');
        }
    }
}