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

            // Supprimer les sections existantes pour ce rapport avant de les recréer/mettre à jour
            $this->sectionRapportModel->supprimerParCles(['id_rapport_etudiant' => $idRapport]);

            foreach ($sections as $titre => $contenu) {
                $this->sectionRapportModel->creer(['id_rapport_etudiant' => $idRapport, 'titre_section' => $titre, 'contenu_section' => $contenu]);
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
        $rapport['sections'] = $this->sectionRapportModel->trouverParCritere(['id_rapport_etudiant' => $idRapport], ['*'], 'AND', 'ordre ASC');
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
            $this->communicationService->envoyerNotificationInterne($rapport['numero_carte_etudiant'], 'STATUT_RAPPORT_FORCE', ['id_rapport' => $idRapport, 'nouveau_statut' => $nouveauStatut, 'justification' => $justification]);
        }
        return $success;
    }

    /**
     * Lit le rapport de l'étudiant pour l'année académique active.
     * @param string $numeroEtudiant
     * @return array|null
     */
    public function lireRapportPourAnneeActive(string $numeroEtudiant): ?array
    {
        $anneeActive = $this->systemeService->getAnneeAcademiqueActive();
        if (!$anneeActive) {
            return null; // Aucune année académique active définie
        }

        // On cherche le rapport le plus récent de l'étudiant pour l'année active
        // ou un rapport en brouillon/correction s'il existe
        $rapport = $this->rapportModel->trouverUnParCritere(
            [
                'numero_carte_etudiant' => $numeroEtudiant,
                'id_statut_rapport' => ['operator' => 'IN', 'value' => ['RAP_BROUILLON', 'RAP_SOUMIS', 'RAP_NON_CONF', 'RAP_CONF', 'RAP_EN_COMMISSION', 'RAP_CORRECT', 'RAP_VALID', 'RAP_REFUSE']]
            ],
            ['*'],
            'AND',
            'date_derniere_modif DESC'
        );

        // Si un rapport est trouvé, on vérifie s'il est lié à l'année active ou s'il est en cours de traitement
        // Pour simplifier, on suppose que le rapport le plus récent est celui pertinent pour l'année active
        // Une logique plus complexe pourrait lier les rapports aux inscriptions par année académique
        return $rapport;
    }

    /**
     * Retourne les étapes du workflow pour un rapport donné, avec leur statut.
     * @param string|null $idRapport
     * @return array
     */
    public function getWorkflowStepsForRapport(?string $idRapport): array
    {
        $steps = $this->systemeService->gererReferentiel('list', 'statut_rapport_ref');
        $currentRapportStatus = null;
        if ($idRapport) {
            $rapport = $this->rapportModel->trouverParIdentifiant($idRapport);
            $currentRapportStatus = $rapport['id_statut_rapport'] ?? null;
        }

        $workflow = [];
        foreach ($steps as $step) {
            // Filtrer les statuts qui ne sont pas des étapes de workflow visibles pour l'étudiant
            if (str_starts_with($step['id_statut_rapport'], 'RAP_') && $step['etape_workflow'] !== null) {
                $workflow[$step['etape_workflow']] = [
                    'id' => $step['id_statut_rapport'],
                    'label' => $step['libelle_statut_rapport'],
                    'completed' => false,
                    'current' => false
                ];
            }
        }

        ksort($workflow); // Trier par ordre d'étape

        $completed = true;
        foreach ($workflow as $key => &$step) {
            if ($step['id'] === $currentRapportStatus) {
                $step['current'] = true;
                $completed = false; // Les étapes suivantes ne sont pas complétées
            }
            if ($completed) {
                $step['completed'] = true;
            }
        }
        unset($step); // Rompre la référence

        return array_values($workflow); // Retourner un tableau indexé numériquement
    }

    /**
     * Liste les modèles de rapport disponibles pour la création.
     * @return array
     */
    public function listerModelesRapportDisponibles(): array
    {
        // Pour l'instant, on liste tous les modèles publiés.
        // On pourrait ajouter une logique pour filtrer par niveau d'étude de l'étudiant.
        return $this->systemeService->gererReferentiel('list', 'rapport_modele');
    }

    /**
     * Crée un nouveau rapport pour un étudiant à partir d'un modèle.
     * @param string $numeroEtudiant
     * @param string $idModele
     * @return string L'ID du nouveau rapport créé.
     * @throws ElementNonTrouveException
     * @throws OperationImpossibleException
     */
    public function creerRapportDepuisModele(string $numeroEtudiant, string $idModele): string
    {
        $modele = $this->systemeService->gererReferentiel('read', 'rapport_modele', $idModele);
        if (!$modele) {
            throw new ElementNonTrouveException("Modèle de rapport '{$idModele}' non trouvé.");
        }

        $sectionsModele = $this->systemeService->gererReferentiel('list', 'rapport_modele_section', ['id_modele' => $idModele]);

        $metadonnees = [
            'libelle_rapport_etudiant' => "Nouveau rapport basé sur le modèle : " . $modele['nom_modele'],
            'theme' => 'Thème à définir',
            'resume' => '<p>Résumé du rapport...</p>',
            'nombre_pages' => 0
        ];

        $sections = [];
        foreach ($sectionsModele as $section) {
            $sections[$section['titre_section']] = $section['contenu_par_defaut'] ?? '';
        }

        return $this->creerOuMettreAJourBrouillon($numeroEtudiant, $metadonnees, $sections);
    }

    // ====================================================================
    // PHASE 2: VÉRIFICATION DE CONFORMITÉ
    // ====================================================================

    public function traiterVerificationConformite(string $idRapport, string $numeroPersonnel, bool $estConforme, array $detailsChecklist, ?string $commentaireGeneral): bool
    {
        $this->db->beginTransaction();
        try {
            // Supprimer les anciens détails de conformité pour ce rapport avant de les recréer
            $this->conformiteDetailsModel->supprimerParCles(['id_rapport_etudiant' => $idRapport]);

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
        // Jointure pour récupérer les rapports associés à cette session
        $sql = "SELECT sr.*, r.libelle_rapport_etudiant, r.numero_carte_etudiant, r.id_statut_rapport, e.nom, e.prenom
                FROM session_rapport sr
                JOIN rapport_etudiant r ON sr.id_rapport_etudiant = r.id_rapport_etudiant
                JOIN etudiant e ON r.numero_carte_etudiant = e.numero_carte_etudiant
                WHERE sr.id_session = :id_session";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id_session' => $idSession]);
        $session['rapports'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $session;
    }

    public function designerRapporteur(string $idRapport, string $numeroEnseignantRapporteur): bool
    {
        // Cette logique dépend de la structure de votre table 'affecter' ou d'une nouvelle table 'rapporteur_rapport'
        // Pour l'exemple, nous allons supposer que 'affecter' peut être utilisée avec un statut spécifique.
        // On vérifie si l'affectation existe déjà pour éviter les doublons
        $existing = $this->affecterModel->trouverUnParCritere([
            'numero_enseignant' => $numeroEnseignantRapporteur,
            'id_rapport_etudiant' => $idRapport,
            'id_statut_jury' => 'JURY_RAPPORTEUR'
        ]);

        if ($existing) {
            return true; // Déjà désigné
        }

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
        $this->db->beginTransaction();
        try {
            // Vérifier si l'utilisateur a déjà voté pour ce rapport dans ce tour
            $currentTour = $this->voteModel->trouverUnParCritere(['id_rapport_etudiant' => $idRapport], ['MAX(tour_vote) as max_tour'])['max_tour'] ?? 1;
            $existingVote = $this->voteModel->trouverUnParCritere([
                'id_rapport_etudiant' => $idRapport,
                'numero_enseignant' => $numeroEnseignant,
                'tour_vote' => $currentTour
            ]);

            if ($existingVote) {
                // Mettre à jour le vote existant
                $success = $this->voteModel->mettreAJourParIdentifiant($existingVote['id_vote'], [
                    'id_decision_vote' => $decision,
                    'commentaire_vote' => $commentaire,
                    'date_vote' => date('Y-m-d H:i:s')
                ]);
            } else {
                // Créer un nouveau vote
                $idVote = $this->systemeService->genererIdentifiantUnique('VOTE');
                $success = (bool) $this->voteModel->creer([
                    'id_vote' => $idVote,
                    'id_session' => $idSession,
                    'id_rapport_etudiant' => $idRapport,
                    'numero_enseignant' => $numeroEnseignant,
                    'id_decision_vote' => $decision,
                    'commentaire_vote' => $commentaire,
                    'tour_vote' => $currentTour
                ]);
            }

            if ($success) {
                $this->db->commit();
                $this->verifierEtFinaliserVote($idRapport, $idSession);
                $this->supervisionService->enregistrerAction($numeroEnseignant, 'ENREGISTREMENT_VOTE', $idRapport, 'RapportEtudiant', ['decision' => $decision, 'session' => $idSession, 'tour' => $currentTour]);
            } else {
                $this->db->rollBack();
            }
            return $success;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function lancerNouveauTourDeVote(string $idRapport, string $idSession): bool
    {
        $tourActuel = $this->voteModel->trouverUnParCritere(['id_rapport_etudiant' => $idRapport], ['MAX(tour_vote) as max_tour'])['max_tour'] ?? 1;
        $nouveauTour = $tourActuel + 1;

        // Logique pour notifier les membres qu'un nouveau tour a commencé
        $this->supervisionService->enregistrerAction($_SESSION['user_id'], 'NOUVEAU_TOUR_VOTE', $idRapport, 'RapportEtudiant', ['session' => $idSession, 'tour' => $nouveauTour]);
        // Le statut du rapport pourrait être mis à jour pour indiquer qu'il est en attente d'un nouveau tour de vote
        // $this->changerStatutRapport($idRapport, 'RAP_EN_COMMISSION', 'GRP_COMMISSION', 'NOUVEAU_TOUR_VOTE');
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

    /**
     * Liste les PV en attente d'approbation pour un utilisateur donné.
     * @param string $numeroUtilisateur
     * @return array
     */
    public function listerPvAApprouver(string $numeroUtilisateur): array
    {
        // On suppose que seuls les membres de la commission peuvent approuver les PV
        // et que le statut est 'PV_ATTENTE_APPROBATION'
        $sql = "SELECT cr.*, sv.nom_session
                FROM compte_rendu cr
                JOIN session_validation sv ON cr.libelle_compte_rendu LIKE CONCAT('%', sv.id_session, '%')
                WHERE cr.id_statut_pv = 'PV_ATTENTE_APPROBATION'
                AND cr.id_redacteur != :user_id"; // Exclure le rédacteur lui-même s'il ne doit pas approuver son propre PV

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => $numeroUtilisateur]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ====================================================================
    // PHASE 6: FINALISATION POST-VALIDATION
    // ====================================================================

    public function designerDirecteurMemoire(string $idRapport, string $numeroEnseignantDirecteur): bool
    {
        // On vérifie si l'affectation existe déjà pour éviter les doublons
        $existing = $this->affecterModel->trouverUnParCritere([
            'numero_enseignant' => $numeroEnseignantDirecteur,
            'id_rapport_etudiant' => $idRapport,
            'directeur_memoire' => 1
        ]);

        if ($existing) {
            return true; // Déjà désigné
        }

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
        $this->communicationService->envoyerNotificationGroupe('GRP_RS', 'NOUVELLE_RECLAMATION', ['sujet_reclamation' => $sujet]);
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
        $success = $this->reclamationModel->mettreAJourParIdentifiant($idReclamation, [
            'reponse_reclamation' => $reponse,
            'date_reponse' => date('Y-m-d H:i:s'),
            'numero_personnel_traitant' => $numeroPersonnel,
            'id_statut_reclamation' => 'RECLA_RESOLUE'
        ]);
        if ($success) {
            $reclamation = $this->reclamationModel->trouverParIdentifiant($idReclamation);
            $this->communicationService->envoyerNotificationInterne($reclamation['numero_carte_etudiant'], 'RECLAMATION_REPONDU', ['sujet_reclamation' => $reclamation['sujet_reclamation']]);
        }
        return $success;
    }

    /**
     * Permet de répondre à une réclamation sans forcément la clôturer.
     * @param string $idReclamation
     * @param string $reponse
     * @param string $numeroPersonnel
     * @return bool
     */
    public function repondreAReclamation(string $idReclamation, string $reponse, string $numeroPersonnel): bool
    {
        $success = $this->reclamationModel->mettreAJourParIdentifiant($idReclamation, [
            'reponse_reclamation' => $reponse,
            'date_reponse' => date('Y-m-d H:i:s'),
            'numero_personnel_traitant' => $numeroPersonnel,
            'id_statut_reclamation' => 'RECLA_EN_COURS' // Statut mis à jour pour indiquer que la réclamation est en cours de traitement
        ]);
        if ($success) {
            $reclamation = $this->reclamationModel->trouverParIdentifiant($idReclamation);
            $this->communicationService->envoyerNotificationInterne($reclamation['numero_carte_etudiant'], 'RECLAMATION_REPONDU', ['sujet_reclamation' => $reclamation['sujet_reclamation']]);
        }
        return $success;
    }

    // --- Méthode privée utilitaire ---
    private function changerStatutRapport(string $idRapport, string $nouveauStatut, ?string $groupeANotifier, ?string $templateNotification): bool
    {
        $rapport = $this->rapportModel->trouverParIdentifiant($idRapport);
        if (!$rapport) throw new ElementNonTrouveException("Rapport non trouvé.");

        $success = $this->rapportModel->mettreAJourParIdentifiant($idRapport, ['id_statut_rapport' => $nouveauStatut]);

        if ($success) {
            $this->supervisionService->enregistrerAction($_SESSION['user_id'] ?? 'SYSTEM', 'CHANGEMENT_STATUT_RAPPORT', $idRapport, 'RapportEtudiant', ['nouveau_statut' => $nouveauStatut]);

            $this->communicationService->envoyerNotificationInterne($rapport['numero_carte_etudiant'], 'STATUT_RAPPORT_MAJ', ['nouveau_statut' => $nouveauStatut]);

            if ($groupeANotifier && $templateNotification) {
                $this->communicationService->envoyerNotificationGroupe($groupeANotifier, $templateNotification, ['id_rapport' => $idRapport]);
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