<?php

namespace App\Backend\Service\WorkflowSoutenance;

use PDO;
use App\Backend\Model\RapportEtudiant;
use App\Backend\Model\SessionValidation;
use App\Backend\Model\SessionRapport;
use App\Backend\Model\VoteCommission;
use App\Backend\Model\Affecter;
use App\Backend\Model\CompteRendu;
use App\Backend\Model\Approuver;
use App\Backend\Service\Commission\ServiceCommissionInterface;
use App\Backend\Service\Conformite\ServiceConformiteInterface;
use App\Backend\Service\DocumentGenerator\ServiceDocumentGeneratorInterface;
use App\Backend\Service\SupervisionAdmin\ServiceSupervisionAdminInterface;
use App\Backend\Service\IdentifiantGenerator\IdentifiantGeneratorInterface;
use App\Backend\Service\Notification\ServiceNotificationInterface;
use App\Backend\Exception\ElementNonTrouveException;
use App\Backend\Exception\ValidationException;
use App\Backend\Exception\OperationImpossibleException;

class ServiceWorkflowSoutenance implements ServiceWorkflowSoutenanceInterface
{
    private PDO $db;
    private RapportEtudiant $rapportModel;
    private SessionValidation $sessionValidationModel;
    private SessionRapport $sessionRapportModel;
    private VoteCommission $voteCommissionModel;
    private Affecter $affecterModel;
    private CompteRendu $compteRenduModel;
    private Approuver $approuverModel;
    private ServiceCommissionInterface $commissionService;
    private ServiceConformiteInterface $conformiteService;
    private ServiceDocumentGeneratorInterface $documentService;
    private ServiceSupervisionAdminInterface $supervisionService;
    private IdentifiantGeneratorInterface $idGenerator;
    private ServiceNotificationInterface $notificationService;

    // États du workflow
    private const ETATS_WORKFLOW = [
        'INITIE' => 'Workflow initié',
        'RAPPORT_SOUMIS' => 'Rapport soumis',
        'CONFORMITE_EN_COURS' => 'Vérification conformité en cours',
        'CONFORMITE_VALIDEE' => 'Conformité validée',
        'JURY_AFFECTE' => 'Jury affecté',
        'SESSION_PROGRAMMEE' => 'Session programmée',
        'VOTE_EN_COURS' => 'Vote en cours',
        'DECISION_FINALISEE' => 'Décision finalisée',
        'DOCUMENTS_GENERES' => 'Documents générés',
        'TERMINE' => 'Terminé',
        'SUSPENDU' => 'Suspendu',
        'ANNULE' => 'Annulé'
    ];

    public function __construct(
        PDO $db,
        RapportEtudiant $rapportModel,
        SessionValidation $sessionValidationModel,
        SessionRapport $sessionRapportModel,
        VoteCommission $voteCommissionModel,
        Affecter $affecterModel,
        CompteRendu $compteRenduModel,
        Approuver $approuverModel,
        ServiceCommissionInterface $commissionService,
        ServiceConformiteInterface $conformiteService,
        ServiceDocumentGeneratorInterface $documentService,
        ServiceSupervisionAdminInterface $supervisionService,
        IdentifiantGeneratorInterface $idGenerator,
        ServiceNotificationInterface $notificationService
    ) {
        $this->db = $db;
        $this->rapportModel = $rapportModel;
        $this->sessionValidationModel = $sessionValidationModel;
        $this->sessionRapportModel = $sessionRapportModel;
        $this->voteCommissionModel = $voteCommissionModel;
        $this->affecterModel = $affecterModel;
        $this->compteRenduModel = $compteRenduModel;
        $this->approuverModel = $approuverModel;
        $this->commissionService = $commissionService;
        $this->conformiteService = $conformiteService;
        $this->documentService = $documentService;
        $this->supervisionService = $supervisionService;
        $this->idGenerator = $idGenerator;
        $this->notificationService = $notificationService;
    }

    public function demarrerWorkflow(string $numeroEtudiant, string $idAnneeAcademique): string
    {
        try {
            $this->db->beginTransaction();

            $idWorkflow = $this->idGenerator->genererProchainId('workflow_soutenance');
            
            // Créer l'entrée de workflow dans la table rapport_etudiant
            $donneesRapport = [
                'id_rapport_etudiant' => $idWorkflow,
                'numero_etudiant' => $numeroEtudiant,
                'id_annee_academique' => $idAnneeAcademique,
                'etat_workflow' => 'INITIE',
                'date_creation_workflow' => date('Y-m-d H:i:s'),
                'id_statut_rapport' => 'EN_PREPARATION'
            ];

            $result = $this->rapportModel->creer($donneesRapport);

            if ($result) {
                $this->supervisionService->enregistrerAction(
                    $_SESSION['numero_utilisateur'] ?? 'SYSTEM',
                    'DEMARRAGE_WORKFLOW',
                    "Démarrage du workflow de soutenance",
                    'workflow',
                    $idWorkflow,
                    $donneesRapport
                );

                $this->notificationService->envoyerNotificationUtilisateur(
                    $numeroEtudiant,
                    'WORKFLOW_DEMARRE',
                    "Le processus de soutenance a été initié",
                    ['workflow_id' => $idWorkflow]
                );
            }

            $this->db->commit();
            return $idWorkflow;

        } catch (\Exception $e) {
            $this->db->rollBack();
            throw new OperationImpossibleException("Impossible de démarrer le workflow: " . $e->getMessage());
        }
    }

    public function soumettreRapport(string $idWorkflow, array $donneesRapport): bool
    {
        try {
            $this->db->beginTransaction();

            $workflow = $this->rapportModel->trouverParId($idWorkflow);
            if (!$workflow) {
                throw new ElementNonTrouveException("Workflow non trouvé: {$idWorkflow}");
            }

            if ($workflow['etat_workflow'] !== 'INITIE') {
                throw new ValidationException("Le rapport a déjà été soumis pour ce workflow.");
            }

            $donneesModification = array_merge($donneesRapport, [
                'etat_workflow' => 'RAPPORT_SOUMIS',
                'date_soumission_rapport' => date('Y-m-d H:i:s'),
                'id_statut_rapport' => 'SOUMIS'
            ]);

            $result = $this->rapportModel->mettreAJour($idWorkflow, $donneesModification);

            if ($result) {
                $this->supervisionService->enregistrerAction(
                    $_SESSION['numero_utilisateur'] ?? 'SYSTEM',
                    'SOUMISSION_RAPPORT',
                    "Soumission du rapport de soutenance",
                    'rapport',
                    $idWorkflow,
                    $donneesRapport
                );

                // Notifier le personnel administratif pour la vérification de conformité
                $this->notificationService->envoyerNotificationGroupe(
                    'PERSONNEL_ADMIN',
                    'NOUVEAU_RAPPORT_A_VERIFIER',
                    "Un nouveau rapport est en attente de vérification de conformité",
                    ['rapport_id' => $idWorkflow]
                );

                // Avancer automatiquement à l'étape suivante
                $this->avancerEtape($idWorkflow, 'CONFORMITE_EN_COURS', []);
            }

            $this->db->commit();
            return $result;

        } catch (\Exception $e) {
            $this->db->rollBack();
            throw new OperationImpossibleException("Impossible de soumettre le rapport: " . $e->getMessage());
        }
    }

    public function validerConformiteAdministrative(string $idRapport, string $numeroPersonnelAdmin, array $resultatsConformite): bool
    {
        try {
            $this->db->beginTransaction();

            $result = $this->conformiteService->validerConformiteRapport(
                $idRapport,
                $numeroPersonnelAdmin,
                $resultatsConformite
            );

            if ($result) {
                $nouvelEtat = $resultatsConformite['conforme'] ? 'CONFORMITE_VALIDEE' : 'CONFORMITE_EN_COURS';
                
                $this->rapportModel->mettreAJour($idRapport, [
                    'etat_workflow' => $nouvelEtat,
                    'date_validation_conformite' => date('Y-m-d H:i:s')
                ]);

                if ($resultatsConformite['conforme']) {
                    // Notifier la commission pour affecter un jury
                    $this->notificationService->envoyerNotificationGroupe(
                        'COMMISSION',
                        'RAPPORT_CONFORME',
                        "Un rapport conforme est en attente d'affectation de jury",
                        ['rapport_id' => $idRapport]
                    );
                } else {
                    // Notifier l'étudiant des corrections à apporter
                    $rapport = $this->rapportModel->trouverParId($idRapport);
                    $this->notificationService->envoyerNotificationUtilisateur(
                        $rapport['numero_etudiant'],
                        'CONFORMITE_NON_VALIDEE',
                        "Votre rapport nécessite des corrections",
                        ['corrections' => $resultatsConformite['commentaires'] ?? '']
                    );
                }
            }

            $this->db->commit();
            return $result;

        } catch (\Exception $e) {
            $this->db->rollBack();
            throw new OperationImpossibleException("Impossible de valider la conformité: " . $e->getMessage());
        }
    }

    public function affecterJury(string $idRapport, array $membresJury): bool
    {
        try {
            $this->db->beginTransaction();

            $result = true;
            foreach ($membresJury as $membre) {
                $idAffectation = $this->idGenerator->genererProchainId('affectation');
                $donneesAffectation = [
                    'id_affectation' => $idAffectation,
                    'id_rapport_etudiant' => $idRapport,
                    'numero_enseignant' => $membre['numero_enseignant'],
                    'role_jury' => $membre['role'],
                    'date_affectation' => date('Y-m-d H:i:s')
                ];

                $result = $result && $this->affecterModel->creer($donneesAffectation);
            }

            if ($result) {
                $this->rapportModel->mettreAJour($idRapport, [
                    'etat_workflow' => 'JURY_AFFECTE',
                    'date_affectation_jury' => date('Y-m-d H:i:s')
                ]);

                $this->supervisionService->enregistrerAction(
                    $_SESSION['numero_utilisateur'] ?? 'SYSTEM',
                    'AFFECTATION_JURY',
                    "Affectation du jury au rapport",
                    'rapport',
                    $idRapport,
                    $membresJury
                );

                // Notifier les membres du jury
                foreach ($membresJury as $membre) {
                    $this->notificationService->envoyerNotificationUtilisateur(
                        $membre['numero_enseignant'],
                        'AFFECTATION_JURY',
                        "Vous avez été affecté comme {$membre['role']} pour un rapport de soutenance",
                        ['rapport_id' => $idRapport, 'role' => $membre['role']]
                    );
                }
            }

            $this->db->commit();
            return $result;

        } catch (\Exception $e) {
            $this->db->rollBack();
            throw new OperationImpossibleException("Impossible d'affecter le jury: " . $e->getMessage());
        }
    }

    public function programmerSessionValidation(array $donneesSession, array $idsRapports): string
    {
        try {
            $this->db->beginTransaction();

            $idSession = $this->commissionService->creerSessionValidation(
                $donneesSession['libelle_session'],
                $donneesSession['date_debut_session'],
                $donneesSession['date_fin_prevue'],
                $donneesSession['numero_president_commission'] ?? null,
                $idsRapports
            );

            // Mettre à jour l'état des rapports
            foreach ($idsRapports as $idRapport) {
                $this->rapportModel->mettreAJour($idRapport, [
                    'etat_workflow' => 'SESSION_PROGRAMMEE',
                    'date_programmation_session' => date('Y-m-d H:i:s')
                ]);
            }

            $this->db->commit();
            return $idSession;

        } catch (\Exception $e) {
            $this->db->rollBack();
            throw new OperationImpossibleException("Impossible de programmer la session: " . $e->getMessage());
        }
    }

    public function enregistrerVote(string $idRapport, string $numeroEnseignant, array $donneesVote): bool
    {
        try {
            $this->db->beginTransaction();

            $result = $this->commissionService->enregistrerVotePourRapport(
                $idRapport,
                $numeroEnseignant,
                $donneesVote['id_decision_vote'],
                $donneesVote['commentaire_vote'] ?? null,
                $donneesVote['tour_vote'] ?? 1
            );

            if ($result) {
                $this->rapportModel->mettreAJour($idRapport, [
                    'etat_workflow' => 'VOTE_EN_COURS',
                    'date_dernier_vote' => date('Y-m-d H:i:s')
                ]);

                $this->supervisionService->enregistrerAction(
                    $numeroEnseignant,
                    'VOTE_ENREGISTRE',
                    "Vote enregistré pour le rapport",
                    'vote',
                    $idRapport,
                    $donneesVote
                );
            }

            $this->db->commit();
            return $result;

        } catch (\Exception $e) {
            $this->db->rollBack();
            throw new OperationImpossibleException("Impossible d'enregistrer le vote: " . $e->getMessage());
        }
    }

    public function finaliserDecisionCommission(string $idRapport): bool
    {
        try {
            $this->db->beginTransaction();

            $result = $this->commissionService->finaliserDecisionCommissionPourRapport($idRapport);

            if ($result) {
                $this->rapportModel->mettreAJour($idRapport, [
                    'etat_workflow' => 'DECISION_FINALISEE',
                    'date_finalisation_decision' => date('Y-m-d H:i:s')
                ]);

                $rapport = $this->rapportModel->trouverParId($idRapport);
                $this->notificationService->envoyerNotificationUtilisateur(
                    $rapport['numero_etudiant'],
                    'DECISION_FINALISEE',
                    "La décision de commission pour votre soutenance a été finalisée",
                    ['rapport_id' => $idRapport]
                );
            }

            $this->db->commit();
            return $result;

        } catch (\Exception $e) {
            $this->db->rollBack();
            throw new OperationImpossibleException("Impossible de finaliser la décision: " . $e->getMessage());
        }
    }

    public function genererDocumentsOfficiels(string $idRapport, array $typesDocuments): array
    {
        try {
            $documentsGeneres = [];

            foreach ($typesDocuments as $typeDocument) {
                switch ($typeDocument) {
                    case 'PV_SOUTENANCE':
                        $cheminDocument = $this->documentService->genererPvSoutenance($idRapport);
                        break;
                    case 'ATTESTATION_REUSSITE':
                        $cheminDocument = $this->documentService->genererAttestationReussite($idRapport);
                        break;
                    case 'BULLETIN_NOTES':
                        $cheminDocument = $this->documentService->genererBulletinNotes($idRapport);
                        break;
                    default:
                        continue 2;
                }

                $documentsGeneres[$typeDocument] = $cheminDocument;
            }

            $this->rapportModel->mettreAJour($idRapport, [
                'etat_workflow' => 'DOCUMENTS_GENERES',
                'date_generation_documents' => date('Y-m-d H:i:s')
            ]);

            $this->supervisionService->enregistrerAction(
                $_SESSION['numero_utilisateur'] ?? 'SYSTEM',
                'GENERATION_DOCUMENTS',
                "Génération des documents officiels",
                'rapport',
                $idRapport,
                ['types_documents' => $typesDocuments]
            );

            return $documentsGeneres;

        } catch (\Exception $e) {
            throw new OperationImpossibleException("Impossible de générer les documents: " . $e->getMessage());
        }
    }

    public function obtenirEtatWorkflow(string $idWorkflow): array
    {
        $workflow = $this->rapportModel->trouverParId($idWorkflow);
        if (!$workflow) {
            throw new ElementNonTrouveException("Workflow non trouvé: {$idWorkflow}");
        }

        // Récupérer les informations additionnelles selon l'état
        $informationsComplementaires = [];

        switch ($workflow['etat_workflow']) {
            case 'JURY_AFFECTE':
            case 'SESSION_PROGRAMMEE':
            case 'VOTE_EN_COURS':
                $informationsComplementaires['jury'] = $this->affecterModel->listerParRapport($idWorkflow);
                $informationsComplementaires['votes'] = $this->voteCommissionModel->listerParRapport($idWorkflow);
                break;
            case 'DECISION_FINALISEE':
            case 'DOCUMENTS_GENERES':
                $informationsComplementaires['decision_finale'] = $this->obtenirDecisionFinale($idWorkflow);
                break;
        }

        return [
            'workflow' => $workflow,
            'etat_libelle' => self::ETATS_WORKFLOW[$workflow['etat_workflow']] ?? 'État inconnu',
            'etapes_completees' => $this->obtenirEtapesCompletees($workflow),
            'prochaines_etapes' => $this->obtenirProchainesEtapes($workflow),
            'informations_complementaires' => $informationsComplementaires
        ];
    }

    public function listerWorkflows(array $criteres = [], int $page = 1, int $elementsParPage = 20): array
    {
        $offset = ($page - 1) * $elementsParPage;
        
        $sql = "SELECT r.*, e.nom_etudiant, e.prenom_etudiant, aa.libelle_annee_academique
                FROM rapport_etudiant r
                JOIN etudiant e ON r.numero_etudiant = e.numero_etudiant
                JOIN annee_academique aa ON r.id_annee_academique = aa.id_annee_academique
                WHERE 1=1";

        $params = [];

        if (!empty($criteres['etat_workflow'])) {
            $sql .= " AND r.etat_workflow = ?";
            $params[] = $criteres['etat_workflow'];
        }

        if (!empty($criteres['annee_academique'])) {
            $sql .= " AND r.id_annee_academique = ?";
            $params[] = $criteres['annee_academique'];
        }

        if (!empty($criteres['etudiant'])) {
            $sql .= " AND (e.nom_etudiant LIKE ? OR e.prenom_etudiant LIKE ?)";
            $terme = '%' . $criteres['etudiant'] . '%';
            $params[] = $terme;
            $params[] = $terme;
        }

        // Compter le total
        $countSql = "SELECT COUNT(*) as total FROM ($sql) as count_query";
        $stmt = $this->db->prepare($countSql);
        $stmt->execute($params);
        $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Récupérer les données paginées
        $sql .= " ORDER BY r.date_creation_workflow DESC LIMIT ? OFFSET ?";
        $params[] = $elementsParPage;
        $params[] = $offset;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $workflows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'workflows' => $workflows,
            'pagination' => [
                'page_actuelle' => $page,
                'elements_par_page' => $elementsParPage,
                'total_elements' => $total,
                'total_pages' => ceil($total / $elementsParPage)
            ]
        ];
    }

    public function avancerEtape(string $idWorkflow, string $etapeSuivante, array $donneesTransition): bool
    {
        try {
            $this->db->beginTransaction();

            if (!array_key_exists($etapeSuivante, self::ETATS_WORKFLOW)) {
                throw new ValidationException("Étape non valide: {$etapeSuivante}");
            }

            $donneesModification = array_merge($donneesTransition, [
                'etat_workflow' => $etapeSuivante,
                'date_derniere_modification' => date('Y-m-d H:i:s')
            ]);

            $result = $this->rapportModel->mettreAJour($idWorkflow, $donneesModification);

            if ($result) {
                $this->supervisionService->enregistrerAction(
                    $_SESSION['numero_utilisateur'] ?? 'SYSTEM',
                    'AVANCEMENT_ETAPE',
                    "Avancement du workflow à l'étape: {$etapeSuivante}",
                    'workflow',
                    $idWorkflow,
                    ['nouvelle_etape' => $etapeSuivante]
                );
            }

            $this->db->commit();
            return $result;

        } catch (\Exception $e) {
            $this->db->rollBack();
            throw new OperationImpossibleException("Impossible d'avancer l'étape: " . $e->getMessage());
        }
    }

    public function gererSuspensionAnnulation(string $idWorkflow, string $action, string $raison): bool
    {
        $actionsValides = ['SUSPENDRE', 'ANNULER'];
        if (!in_array($action, $actionsValides)) {
            throw new ValidationException("Action non valide: {$action}");
        }

        try {
            $this->db->beginTransaction();

            $nouvelEtat = $action === 'SUSPENDRE' ? 'SUSPENDU' : 'ANNULE';
            
            $result = $this->rapportModel->mettreAJour($idWorkflow, [
                'etat_workflow' => $nouvelEtat,
                'raison_suspension_annulation' => $raison,
                'date_suspension_annulation' => date('Y-m-d H:i:s')
            ]);

            if ($result) {
                $this->supervisionService->enregistrerAction(
                    $_SESSION['numero_utilisateur'] ?? 'SYSTEM',
                    "WORKFLOW_{$action}",
                    "Workflow {$action} - Raison: {$raison}",
                    'workflow',
                    $idWorkflow,
                    ['action' => $action, 'raison' => $raison]
                );

                $workflow = $this->rapportModel->trouverParId($idWorkflow);
                $this->notificationService->envoyerNotificationUtilisateur(
                    $workflow['numero_etudiant'],
                    "WORKFLOW_{$action}",
                    "Votre processus de soutenance a été {$nouvelEtat}",
                    ['raison' => $raison]
                );
            }

            $this->db->commit();
            return $result;

        } catch (\Exception $e) {
            $this->db->rollBack();
            throw new OperationImpossibleException("Impossible de gérer la suspension/annulation: " . $e->getMessage());
        }
    }

    private function obtenirEtapesCompletees(array $workflow): array
    {
        $etapesCompletees = ['INITIE'];
        
        if (!empty($workflow['date_soumission_rapport'])) {
            $etapesCompletees[] = 'RAPPORT_SOUMIS';
        }
        if (!empty($workflow['date_validation_conformite'])) {
            $etapesCompletees[] = 'CONFORMITE_VALIDEE';
        }
        if (!empty($workflow['date_affectation_jury'])) {
            $etapesCompletees[] = 'JURY_AFFECTE';
        }
        if (!empty($workflow['date_programmation_session'])) {
            $etapesCompletees[] = 'SESSION_PROGRAMMEE';
        }
        if (!empty($workflow['date_finalisation_decision'])) {
            $etapesCompletees[] = 'DECISION_FINALISEE';
        }
        if (!empty($workflow['date_generation_documents'])) {
            $etapesCompletees[] = 'DOCUMENTS_GENERES';
        }

        return $etapesCompletees;
    }

    private function obtenirProchainesEtapes(array $workflow): array
    {
        $etatActuel = $workflow['etat_workflow'];
        
        $prochainesEtapes = match ($etatActuel) {
            'INITIE' => ['RAPPORT_SOUMIS'],
            'RAPPORT_SOUMIS' => ['CONFORMITE_EN_COURS'],
            'CONFORMITE_EN_COURS' => ['CONFORMITE_VALIDEE'],
            'CONFORMITE_VALIDEE' => ['JURY_AFFECTE'],
            'JURY_AFFECTE' => ['SESSION_PROGRAMMEE'],
            'SESSION_PROGRAMMEE' => ['VOTE_EN_COURS'],
            'VOTE_EN_COURS' => ['DECISION_FINALISEE'],
            'DECISION_FINALISEE' => ['DOCUMENTS_GENERES'],
            'DOCUMENTS_GENERES' => ['TERMINE'],
            default => []
        };

        return $prochainesEtapes;
    }

    private function obtenirDecisionFinale(string $idRapport): ?array
    {
        $sql = "SELECT * FROM vote_commission 
                WHERE id_rapport_etudiant = ? 
                ORDER BY tour_vote DESC, date_vote DESC 
                LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$idRapport]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
}