<?php

namespace App\Backend\Service\ParcoursAcademique;

use PDO;
use App\Backend\Model\Inscrire;
use App\Backend\Model\Evaluer;
use App\Backend\Model\FaireStage;
use App\Backend\Model\Acquerir;
use App\Backend\Model\Penalite;
use App\Backend\Model\Etudiant;
use App\Backend\Model\AnneeAcademique;
use App\Backend\Model\Ecue;
use App\Backend\Model\NiveauEtude;
use App\Backend\Model\DecisionPassageRef;
use App\Backend\Service\SupervisionAdmin\ServiceSupervisionAdminInterface;
use App\Backend\Service\IdentifiantGenerator\IdentifiantGeneratorInterface;
use App\Backend\Service\Notification\ServiceNotificationInterface;
use App\Backend\Exception\ElementNonTrouveException;
use App\Backend\Exception\ValidationException;
use App\Backend\Exception\OperationImpossibleException;

class ServiceParcoursAcademique implements ServiceParcoursAcademiqueInterface
{
    private PDO $db;
    private Inscrire $inscrireModel;
    private Evaluer $evaluerModel;
    private FaireStage $faireStageModel;
    private Acquerir $acquerirModel;
    private Penalite $penaliteModel;
    private Etudiant $etudiantModel;
    private AnneeAcademique $anneeAcademiqueModel;
    private Ecue $ecueModel;
    private NiveauEtude $niveauEtudeModel;
    private DecisionPassageRef $decisionPassageModel;
    private ServiceSupervisionAdminInterface $supervisionService;
    private IdentifiantGeneratorInterface $idGenerator;
    private ServiceNotificationInterface $notificationService;

    public function __construct(
        PDO $db,
        Inscrire $inscrireModel,
        Evaluer $evaluerModel,
        FaireStage $faireStageModel,
        Acquerir $acquerirModel,
        Penalite $penaliteModel,
        Etudiant $etudiantModel,
        AnneeAcademique $anneeAcademiqueModel,
        Ecue $ecueModel,
        NiveauEtude $niveauEtudeModel,
        DecisionPassageRef $decisionPassageModel,
        ServiceSupervisionAdminInterface $supervisionService,
        IdentifiantGeneratorInterface $idGenerator,
        ServiceNotificationInterface $notificationService
    ) {
        $this->db = $db;
        $this->inscrireModel = $inscrireModel;
        $this->evaluerModel = $evaluerModel;
        $this->faireStageModel = $faireStageModel;
        $this->acquerirModel = $acquerirModel;
        $this->penaliteModel = $penaliteModel;
        $this->etudiantModel = $etudiantModel;
        $this->anneeAcademiqueModel = $anneeAcademiqueModel;
        $this->ecueModel = $ecueModel;
        $this->niveauEtudeModel = $niveauEtudeModel;
        $this->decisionPassageModel = $decisionPassageModel;
        $this->supervisionService = $supervisionService;
        $this->idGenerator = $idGenerator;
        $this->notificationService = $notificationService;
    }

    public function inscrireEtudiant(string $numeroEtudiant, string $idAnneeAcademique, array $donneesInscription): string
    {
        try {
            $this->db->beginTransaction();

            // Vérifier que l'étudiant existe
            $etudiant = $this->etudiantModel->trouverParNumero($numeroEtudiant);
            if (!$etudiant) {
                throw new ElementNonTrouveException("Étudiant non trouvé: {$numeroEtudiant}");
            }

            // Vérifier que l'année académique existe
            $anneeAcademique = $this->anneeAcademiqueModel->trouverParId($idAnneeAcademique);
            if (!$anneeAcademique) {
                throw new ElementNonTrouveException("Année académique non trouvée: {$idAnneeAcademique}");
            }

            // Vérifier qu'il n'y a pas déjà une inscription pour cette année
            $inscriptionExistante = $this->inscrireModel->trouverParEtudiantEtAnnee($numeroEtudiant, $idAnneeAcademique);
            if ($inscriptionExistante) {
                throw new ValidationException("L'étudiant est déjà inscrit pour cette année académique.");
            }

            $idInscription = $this->idGenerator->genererProchainId('inscription');
            
            $donneesCompletes = array_merge($donneesInscription, [
                'id_inscription' => $idInscription,
                'numero_etudiant' => $numeroEtudiant,
                'id_annee_academique' => $idAnneeAcademique,
                'date_inscription' => date('Y-m-d H:i:s'),
                'statut_inscription' => 'ACTIVE'
            ]);

            $result = $this->inscrireModel->creer($donneesCompletes);

            if ($result) {
                $this->supervisionService->enregistrerAction(
                    $_SESSION['numero_utilisateur'] ?? 'SYSTEM',
                    'INSCRIPTION_ETUDIANT',
                    "Inscription de l'étudiant pour l'année académique {$idAnneeAcademique}",
                    'inscription',
                    $idInscription,
                    $donneesCompletes
                );

                // Notifier l'étudiant
                $this->notificationService->envoyerNotificationUtilisateur(
                    $etudiant['numero_utilisateur'],
                    'INSCRIPTION_CONFIRMEE',
                    "Votre inscription pour l'année {$anneeAcademique['libelle_annee_academique']} a été confirmée",
                    ['annee_academique' => $anneeAcademique['libelle_annee_academique']]
                );
            }

            $this->db->commit();
            return $idInscription;

        } catch (\Exception $e) {
            $this->db->rollBack();
            throw new OperationImpossibleException("Impossible d'inscrire l'étudiant: " . $e->getMessage());
        }
    }

    public function mettreAJourParcours(string $numeroEtudiant, array $donneesModification): bool
    {
        try {
            $this->db->beginTransaction();

            // Mettre à jour les informations de l'étudiant
            $result = $this->etudiantModel->mettreAJour($numeroEtudiant, $donneesModification);

            if ($result) {
                $this->supervisionService->enregistrerAction(
                    $_SESSION['numero_utilisateur'] ?? 'SYSTEM',
                    'MODIFICATION_PARCOURS',
                    "Modification du parcours académique de l'étudiant",
                    'etudiant',
                    $numeroEtudiant,
                    $donneesModification
                );
            }

            $this->db->commit();
            return $result;

        } catch (\Exception $e) {
            $this->db->rollBack();
            throw new OperationImpossibleException("Impossible de mettre à jour le parcours: " . $e->getMessage());
        }
    }

    public function obtenirParcoursComplet(string $numeroEtudiant): array
    {
        $etudiant = $this->etudiantModel->trouverParNumero($numeroEtudiant);
        if (!$etudiant) {
            throw new ElementNonTrouveException("Étudiant non trouvé: {$numeroEtudiant}");
        }

        // Récupérer les inscriptions
        $inscriptions = $this->inscrireModel->listerParEtudiant($numeroEtudiant);

        // Récupérer les évaluations
        $evaluations = $this->evaluerModel->listerParEtudiant($numeroEtudiant);

        // Récupérer les stages
        $stages = $this->faireStageModel->listerParEtudiant($numeroEtudiant);

        // Récupérer les compétences acquises
        $competences = $this->acquerirModel->listerParEtudiant($numeroEtudiant);

        // Récupérer les pénalités
        $penalites = $this->penaliteModel->listerParEtudiant($numeroEtudiant);

        return [
            'etudiant' => $etudiant,
            'inscriptions' => $inscriptions,
            'evaluations' => $evaluations,
            'stages' => $stages,
            'competences' => $competences,
            'penalites' => $penalites,
            'progression' => $this->calculerProgression($numeroEtudiant)
        ];
    }

    public function enregistrerEvaluation(string $numeroEtudiant, string $idEcue, array $donneesEvaluation): bool
    {
        try {
            $this->db->beginTransaction();

            $idEvaluation = $this->idGenerator->genererProchainId('evaluation');
            
            $donneesCompletes = array_merge($donneesEvaluation, [
                'id_evaluation' => $idEvaluation,
                'numero_etudiant' => $numeroEtudiant,
                'id_ecue' => $idEcue,
                'date_evaluation' => date('Y-m-d H:i:s')
            ]);

            $result = $this->evaluerModel->creer($donneesCompletes);

            if ($result) {
                $this->supervisionService->enregistrerAction(
                    $_SESSION['numero_utilisateur'] ?? 'SYSTEM',
                    'ENREGISTREMENT_EVALUATION',
                    "Enregistrement d'une évaluation pour l'ECUE {$idEcue}",
                    'evaluation',
                    $idEvaluation,
                    $donneesCompletes
                );

                // Notifier l'étudiant si la note est disponible
                if (isset($donneesEvaluation['note_obtenue'])) {
                    $ecue = $this->ecueModel->trouverParId($idEcue);
                    $etudiant = $this->etudiantModel->trouverParNumero($numeroEtudiant);
                    
                    $this->notificationService->envoyerNotificationUtilisateur(
                        $etudiant['numero_utilisateur'],
                        'NOUVELLE_NOTE',
                        "Une nouvelle note a été enregistrée pour l'ECUE {$ecue['libelle_ecue']}",
                        ['ecue' => $ecue['libelle_ecue'], 'note' => $donneesEvaluation['note_obtenue']]
                    );
                }
            }

            $this->db->commit();
            return $result;

        } catch (\Exception $e) {
            $this->db->rollBack();
            throw new OperationImpossibleException("Impossible d'enregistrer l'évaluation: " . $e->getMessage());
        }
    }

    public function enregistrerStage(string $numeroEtudiant, array $donneesStage): string
    {
        try {
            $this->db->beginTransaction();

            $idStage = $this->idGenerator->genererProchainId('stage');
            
            $donneesCompletes = array_merge($donneesStage, [
                'id_stage' => $idStage,
                'numero_etudiant' => $numeroEtudiant,
                'date_creation' => date('Y-m-d H:i:s'),
                'statut_stage' => 'EN_COURS'
            ]);

            $result = $this->faireStageModel->creer($donneesCompletes);

            if ($result) {
                $this->supervisionService->enregistrerAction(
                    $_SESSION['numero_utilisateur'] ?? 'SYSTEM',
                    'ENREGISTREMENT_STAGE',
                    "Enregistrement d'un nouveau stage",
                    'stage',
                    $idStage,
                    $donneesCompletes
                );

                $etudiant = $this->etudiantModel->trouverParNumero($numeroEtudiant);
                $this->notificationService->envoyerNotificationUtilisateur(
                    $etudiant['numero_utilisateur'],
                    'STAGE_ENREGISTRE',
                    "Votre stage a été enregistré avec succès",
                    ['entreprise' => $donneesStage['entreprise'] ?? 'Non spécifiée']
                );
            }

            $this->db->commit();
            return $idStage;

        } catch (\Exception $e) {
            $this->db->rollBack();
            throw new OperationImpossibleException("Impossible d'enregistrer le stage: " . $e->getMessage());
        }
    }

    public function gererAcquisitionCompetence(string $numeroEtudiant, string $idCompetence, array $donneesAcquisition): bool
    {
        try {
            $this->db->beginTransaction();

            $idAcquisition = $this->idGenerator->genererProchainId('acquisition');
            
            $donneesCompletes = array_merge($donneesAcquisition, [
                'id_acquisition' => $idAcquisition,
                'numero_etudiant' => $numeroEtudiant,
                'id_competence' => $idCompetence,
                'date_acquisition' => date('Y-m-d H:i:s')
            ]);

            $result = $this->acquerirModel->creer($donneesCompletes);

            if ($result) {
                $this->supervisionService->enregistrerAction(
                    $_SESSION['numero_utilisateur'] ?? 'SYSTEM',
                    'ACQUISITION_COMPETENCE',
                    "Acquisition d'une nouvelle compétence",
                    'acquisition',
                    $idAcquisition,
                    $donneesCompletes
                );
            }

            $this->db->commit();
            return $result;

        } catch (\Exception $e) {
            $this->db->rollBack();
            throw new OperationImpossibleException("Impossible de gérer l'acquisition de compétence: " . $e->getMessage());
        }
    }

    public function calculerProgression(string $numeroEtudiant, ?string $idAnneeAcademique = null): array
    {
        $whereClause = "WHERE numero_etudiant = ?";
        $params = [$numeroEtudiant];

        if ($idAnneeAcademique) {
            $whereClause .= " AND id_annee_academique = ?";
            $params[] = $idAnneeAcademique;
        }

        // Calculer la moyenne générale
        $sql = "SELECT AVG(note_obtenue) as moyenne_generale, 
                       COUNT(*) as nombre_evaluations,
                       COUNT(CASE WHEN note_obtenue >= 10 THEN 1 END) as evaluations_reussies
                FROM evaluer e
                JOIN inscrire i ON e.numero_etudiant = i.numero_etudiant
                {$whereClause}";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $progression = $stmt->fetch(PDO::FETCH_ASSOC);

        // Calculer le taux de réussite
        $progression['taux_reussite'] = $progression['nombre_evaluations'] > 0 
            ? ($progression['evaluations_reussies'] / $progression['nombre_evaluations']) * 100 
            : 0;

        // Compter les crédits acquis
        $sql = "SELECT SUM(e.credits_ecue) as credits_acquis
                FROM evaluer ev
                JOIN ecue e ON ev.id_ecue = e.id_ecue
                JOIN inscrire i ON ev.numero_etudiant = i.numero_etudiant
                {$whereClause} AND ev.note_obtenue >= 10";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $credits = $stmt->fetch(PDO::FETCH_ASSOC);
        $progression['credits_acquis'] = $credits['credits_acquis'] ?? 0;

        return $progression;
    }

    public function verifierEligibiliteSoutenance(string $numeroEtudiant, string $idAnneeAcademique): array
    {
        $progression = $this->calculerProgression($numeroEtudiant, $idAnneeAcademique);
        $penalites = $this->listerPenalites($numeroEtudiant, true);

        $criteres = [
            'moyenne_minimum' => $progression['moyenne_generale'] >= 10,
            'credits_suffisants' => $progression['credits_acquis'] >= 60, // Par exemple
            'pas_de_penalites_bloquantes' => empty($penalites),
            'stage_effectue' => $this->verifierStageEffectue($numeroEtudiant, $idAnneeAcademique)
        ];

        $eligible = array_reduce($criteres, fn($carry, $item) => $carry && $item, true);

        return [
            'eligible' => $eligible,
            'criteres' => $criteres,
            'progression' => $progression,
            'penalites' => $penalites
        ];
    }

    public function genererReleveNotes(string $numeroEtudiant, string $idAnneeAcademique): array
    {
        $sql = "SELECT e.*, ec.libelle_ecue, ec.credits_ecue, ev.note_obtenue, ev.date_evaluation
                FROM evaluer ev
                JOIN ecue ec ON ev.id_ecue = ec.id_ecue
                JOIN inscrire i ON ev.numero_etudiant = i.numero_etudiant
                WHERE ev.numero_etudiant = ? AND i.id_annee_academique = ?
                ORDER BY ec.libelle_ecue";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$numeroEtudiant, $idAnneeAcademique]);
        $notes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $etudiant = $this->etudiantModel->trouverParNumero($numeroEtudiant);
        $anneeAcademique = $this->anneeAcademiqueModel->trouverParId($idAnneeAcademique);
        $progression = $this->calculerProgression($numeroEtudiant, $idAnneeAcademique);

        return [
            'etudiant' => $etudiant,
            'annee_academique' => $anneeAcademique,
            'notes' => $notes,
            'progression' => $progression,
            'date_generation' => date('Y-m-d H:i:s')
        ];
    }

    public function appliquerPenalite(string $numeroEtudiant, array $donneesPenalite): string
    {
        try {
            $this->db->beginTransaction();

            $idPenalite = $this->idGenerator->genererProchainId('penalite');
            
            $donneesCompletes = array_merge($donneesPenalite, [
                'id_penalite' => $idPenalite,
                'numero_etudiant' => $numeroEtudiant,
                'date_application' => date('Y-m-d H:i:s'),
                'statut_penalite' => 'ACTIVE'
            ]);

            $result = $this->penaliteModel->creer($donneesCompletes);

            if ($result) {
                $this->supervisionService->enregistrerAction(
                    $_SESSION['numero_utilisateur'] ?? 'SYSTEM',
                    'APPLICATION_PENALITE',
                    "Application d'une pénalité à l'étudiant",
                    'penalite',
                    $idPenalite,
                    $donneesCompletes
                );

                $etudiant = $this->etudiantModel->trouverParNumero($numeroEtudiant);
                $this->notificationService->envoyerNotificationUtilisateur(
                    $etudiant['numero_utilisateur'],
                    'PENALITE_APPLIQUEE',
                    "Une pénalité a été appliquée à votre dossier",
                    ['motif' => $donneesPenalite['motif_penalite'] ?? '']
                );
            }

            $this->db->commit();
            return $idPenalite;

        } catch (\Exception $e) {
            $this->db->rollBack();
            throw new OperationImpossibleException("Impossible d'appliquer la pénalité: " . $e->getMessage());
        }
    }

    public function listerPenalites(string $numeroEtudiant, bool $uniquementActives = false): array
    {
        $sql = "SELECT * FROM penalite WHERE numero_etudiant = ?";
        $params = [$numeroEtudiant];

        if ($uniquementActives) {
            $sql .= " AND statut_penalite = 'ACTIVE'";
        }

        $sql .= " ORDER BY date_application DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function validerPassageNiveau(string $numeroEtudiant, string $idDecisionPassage, array $donneesValidation): bool
    {
        try {
            $this->db->beginTransaction();

            // Mettre à jour le niveau de l'étudiant
            $donneesEtudiant = [
                'id_niveau_etude' => $donneesValidation['nouveau_niveau'],
                'date_passage_niveau' => date('Y-m-d H:i:s')
            ];

            $result = $this->etudiantModel->mettreAJour($numeroEtudiant, $donneesEtudiant);

            if ($result) {
                $this->supervisionService->enregistrerAction(
                    $_SESSION['numero_utilisateur'] ?? 'SYSTEM',
                    'VALIDATION_PASSAGE_NIVEAU',
                    "Validation du passage au niveau supérieur",
                    'etudiant',
                    $numeroEtudiant,
                    array_merge($donneesValidation, ['decision' => $idDecisionPassage])
                );

                $etudiant = $this->etudiantModel->trouverParNumero($numeroEtudiant);
                $this->notificationService->envoyerNotificationUtilisateur(
                    $etudiant['numero_utilisateur'],
                    'PASSAGE_NIVEAU_VALIDE',
                    "Votre passage au niveau supérieur a été validé",
                    ['nouveau_niveau' => $donneesValidation['nouveau_niveau']]
                );
            }

            $this->db->commit();
            return $result;

        } catch (\Exception $e) {
            $this->db->rollBack();
            throw new OperationImpossibleException("Impossible de valider le passage de niveau: " . $e->getMessage());
        }
    }

    private function verifierStageEffectue(string $numeroEtudiant, string $idAnneeAcademique): bool
    {
        $sql = "SELECT COUNT(*) as count 
                FROM faire_stage fs
                JOIN inscrire i ON fs.numero_etudiant = i.numero_etudiant
                WHERE fs.numero_etudiant = ? 
                AND i.id_annee_academique = ? 
                AND fs.statut_stage = 'TERMINE'";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$numeroEtudiant, $idAnneeAcademique]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result['count'] > 0;
    }
}