<?php
// src/Backend/Service/ParcoursAcademique/ServiceParcoursAcademique.php

namespace App\Backend\Service\ParcoursAcademique;

use PDO;
use App\Backend\Model\GenericModel;
use App\Backend\Service\Systeme\ServiceSystemeInterface;
use App\Backend\Service\Supervision\ServiceSupervisionInterface;
use App\Backend\Exception\{ElementNonTrouveException, OperationImpossibleException};

class ServiceParcoursAcademique implements ServiceParcoursAcademiqueInterface
{
    private PDO $db;
    private GenericModel $inscrireModel;
    private GenericModel $evaluerModel;
    private GenericModel $faireStageModel;
    private GenericModel $penaliteModel;
    private GenericModel $ueModel;
    private GenericModel $ecueModel;
    private ServiceSystemeInterface $systemeService;
    private ServiceSupervisionInterface $supervisionService;

    public function __construct(
        PDO $db,
        GenericModel $inscrireModel,
        GenericModel $evaluerModel,
        GenericModel $faireStageModel,
        GenericModel $penaliteModel,
        GenericModel $ueModel,
        GenericModel $ecueModel,
        ServiceSystemeInterface $systemeService,
        ServiceSupervisionInterface $supervisionService
    ) {
        $this->db = $db;
        $this->inscrireModel = $inscrireModel;
        $this->evaluerModel = $evaluerModel;
        $this->faireStageModel = $faireStageModel;
        $this->penaliteModel = $penaliteModel;
        $this->ueModel = $ueModel;
        $this->ecueModel = $ecueModel;
        $this->systemeService = $systemeService;
        $this->supervisionService = $supervisionService;
    }

    // --- CRUD Inscriptions ---
    public function creerInscription(array $donnees): bool {
        $donnees['date_inscription'] = date('Y-m-d H:i:s');
        return (bool) $this->inscrireModel->creer($donnees);
    }
    public function lireInscription(string $numeroEtudiant, string $idNiveau, string $idAnnee): ?array {
        return $this->inscrireModel->trouverUnParCritere(['numero_carte_etudiant' => $numeroEtudiant, 'id_niveau_etude' => $idNiveau, 'id_annee_academique' => $idAnnee]);
    }
    public function mettreAJourInscription(string $numeroEtudiant, string $idNiveau, string $idAnnee, array $donnees): bool {
        return $this->inscrireModel->mettreAJourParCles(['numero_carte_etudiant' => $numeroEtudiant, 'id_niveau_etude' => $idNiveau, 'id_annee_academique' => $idAnnee], $donnees);
    }
    public function supprimerInscription(string $numeroEtudiant, string $idNiveau, string $idAnnee): bool {
        return $this->inscrireModel->supprimerParCles(['numero_carte_etudiant' => $numeroEtudiant, 'id_niveau_etude' => $idNiveau, 'id_annee_academique' => $idAnnee]);
    }
    public function listerInscriptions(array $filtres = []): array {
        return $this->inscrireModel->trouverParCritere($filtres);
    }

    // --- CRUD Notes ---
    public function creerOuMettreAJourNote(array $donnees): bool {
        $existing = $this->lireNote($donnees['numero_carte_etudiant'], $donnees['id_ecue'], $donnees['id_annee_academique']);
        if ($existing) {
            return $this->evaluerModel->mettreAJourParCles(['numero_carte_etudiant' => $donnees['numero_carte_etudiant'], 'id_ecue' => $donnees['id_ecue'], 'id_annee_academique' => $donnees['id_annee_academique']], ['note' => $donnees['note']]);
        } else {
            $donnees['date_evaluation'] = date('Y-m-d H:i:s');
            return (bool) $this->evaluerModel->creer($donnees);
        }
    }
    public function lireNote(string $numeroEtudiant, string $idEcue, string $idAnnee): ?array {
        return $this->evaluerModel->trouverUnParCritere(['numero_carte_etudiant' => $numeroEtudiant, 'id_ecue' => $idEcue, 'id_annee_academique' => $idAnnee]);
    }
    public function supprimerNote(string $numeroEtudiant, string $idEcue, string $idAnnee): bool {
        return $this->evaluerModel->supprimerParCles(['numero_carte_etudiant' => $numeroEtudiant, 'id_ecue' => $idEcue, 'id_annee_academique' => $idAnnee]);
    }
    public function listerNotes(array $filtres = []): array {
        return $this->evaluerModel->trouverParCritere($filtres);
    }

    // --- CRUD Stages ---
    public function creerStage(array $donnees): bool { return (bool) $this->faireStageModel->creer($donnees); }
    public function lireStage(string $numeroEtudiant, string $idEntreprise): ?array { return $this->faireStageModel->trouverUnParCritere(['numero_carte_etudiant' => $numeroEtudiant, 'id_entreprise' => $idEntreprise]); }
    public function mettreAJourStage(string $numeroEtudiant, string $idEntreprise, array $donnees): bool { return $this->faireStageModel->mettreAJourParCles(['numero_carte_etudiant' => $numeroEtudiant, 'id_entreprise' => $idEntreprise], $donnees); }
    public function supprimerStage(string $numeroEtudiant, string $idEntreprise): bool { return $this->faireStageModel->supprimerParCles(['numero_carte_etudiant' => $numeroEtudiant, 'id_entreprise' => $idEntreprise]); }

    public function validerStage(string $numeroEtudiant, string $idEntreprise): bool {
        // La logique reste simple : la présence de l'enregistrement vaut validation.
        // On se contente de tracer l'action.
        $this->supervisionService->enregistrerAction($_SESSION['user_id'] ?? 'SYSTEM', 'VALIDATION_STAGE', $numeroEtudiant, 'Etudiant', ['entreprise' => $idEntreprise]);
        return true;
    }

    // --- CRUD Pénalités ---
    public function creerPenalite(array $donnees): string {
        $donnees['id_penalite'] = $this->systemeService->genererIdentifiantUnique('PEN');
        $donnees['id_statut_penalite'] = 'PEN_DUE';
        $donnees['date_creation'] = date('Y-m-d H:i:s');
        $this->penaliteModel->creer($donnees);
        return $donnees['id_penalite'];
    }
    public function lirePenalite(string $idPenalite): ?array { return $this->penaliteModel->trouverParIdentifiant($idPenalite); }
    public function mettreAJourPenalite(string $idPenalite, array $donnees): bool { return $this->penaliteModel->mettreAJourParIdentifiant($idPenalite, $donnees); }

    public function regulariserPenalite(string $idPenalite, string $numeroPersonnel): bool {
        return $this->mettreAJourPenalite($idPenalite, [
            'id_statut_penalite' => 'PEN_REGLEE',
            'date_regularisation' => date('Y-m-d H:i:s'),
            'numero_personnel_traitant' => $numeroPersonnel
        ]);
    }
    public function listerPenalites(array $filtres = []): array { return $this->penaliteModel->trouverParCritere($filtres); }

    // --- Logique Métier ---
    public function estEtudiantEligibleSoumission(string $numeroEtudiant): bool
    {
        $anneeActive = $this->systemeService->getAnneeAcademiqueActive();
        if (!$anneeActive) return false;

        // 1. Vérifier si l'étudiant est bien inscrit et a payé pour l'année active
        $derniereInscription = $this->inscrireModel->trouverUnParCritere(
            ['numero_carte_etudiant' => $numeroEtudiant],
            ['*'],
            'AND',
            'id_annee_academique DESC'
        );

        // Si pas d'inscription du tout, il n'est pas éligible.
        if (!$derniereInscription) return false;

        // **Logique améliorée :** On vérifie si la dernière inscription est de niveau Master 2
        // et si le paiement est en règle.
        // Note : 'M2' est un exemple, il faudrait utiliser l'ID réel du niveau Master 2.
        if ($derniereInscription['id_niveau_etude'] !== 'ID_MASTER_2' || $derniereInscription['id_statut_paiement'] !== 'PAIE_OK') {
            return false;
        }

        // 2. Vérifier si un stage a été validé (la simple existence suffit selon notre règle)
        $stage = $this->faireStageModel->trouverUnParCritere(['numero_carte_etudiant' => $numeroEtudiant]);
        if (!$stage) {
            return false;
        }

        // 3. Vérifier s'il n'y a pas de pénalités non réglées
        $penalitesNonReglees = $this->penaliteModel->trouverUnParCritere(['numero_carte_etudiant' => $numeroEtudiant, 'id_statut_penalite' => 'PEN_DUE']);
        if ($penalitesNonReglees) {
            return false;
        }

        return true;
    }

    public function enregistrerDecisionPassage(string $numeroEtudiant, string $idAnnee, string $idDecision): bool
    {
        $inscription = $this->inscrireModel->trouverUnParCritere(['numero_carte_etudiant' => $numeroEtudiant, 'id_annee_academique' => $idAnnee]);
        if (!$inscription) {
            throw new ElementNonTrouveException("Aucune inscription trouvée pour cet étudiant pour l'année spécifiée.");
        }

        $this->db->beginTransaction();
        try {
            // Mettre à jour la décision sur l'inscription actuelle
            $this->inscrireModel->mettreAJourParCles(
                ['numero_carte_etudiant' => $numeroEtudiant, 'id_niveau_etude' => $inscription['id_niveau_etude'], 'id_annee_academique' => $idAnnee],
                ['id_decision_passage' => $idDecision]
            );

            // Si la décision est "Redoublant", créer une nouvelle inscription pour l'année suivante
            if ($idDecision === 'DEC_REDOUBLANT') {
                $anneeActuelle = (int) substr($idAnnee, 6, 4);
                $anneeSuivante = $anneeActuelle + 1;
                $idAnneeSuivante = "ANNEE-{$anneeActuelle}-{$anneeSuivante}";

                // Vérifier si l'année suivante existe, sinon la créer serait une bonne pratique (via ServiceSysteme)

                $this->creerInscription([
                    'numero_carte_etudiant' => $numeroEtudiant,
                    'id_niveau_etude' => $inscription['id_niveau_etude'],
                    'id_annee_academique' => $idAnneeSuivante,
                    'montant_inscription' => $inscription['montant_inscription'], // ou un montant par défaut
                    'id_statut_paiement' => 'PAIE_ATTENTE',
                    'id_decision_passage' => null
                ]);
            }

            $this->db->commit();
            $this->supervisionService->enregistrerAction($_SESSION['user_id'], 'ENREGISTREMENT_DECISION_PASSAGE', $numeroEtudiant, 'Etudiant', ['decision' => $idDecision]);
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function calculerMoyennes(string $numeroEtudiant, string $idAnnee): array
    {
        $sql = "SELECT 
                    e.note, 
                    ec.credits_ecue, 
                    ec.id_ue,
                    u.libelle_ue,
                    u.credits_ue
                FROM evaluer e
                JOIN ecue ec ON e.id_ecue = ec.id_ecue
                JOIN ue u ON ec.id_ue = u.id_ue
                WHERE e.numero_carte_etudiant = :etudiant AND e.id_annee_academique = :annee";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':etudiant' => $numeroEtudiant, ':annee' => $idAnnee]);
        $notes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($notes)) {
            return ['moyenne_generale' => 0, 'credits_valides' => 0, 'details_ue' => []];
        }

        $moyennesUE = [];
        $totalPondere = 0;
        $totalCredits = 0;
        $totalCreditsValides = 0;

        // Agréger les notes par UE
        foreach ($notes as $note) {
            if (!isset($moyennesUE[$note['id_ue']])) {
                $moyennesUE[$note['id_ue']] = [
                    'libelle' => $note['libelle_ue'],
                    'total_notes' => 0,
                    'total_credits_ecue' => 0,
                    'credits_ue' => (float) $note['credits_ue'],
                    'moyenne' => 0
                ];
            }
            $moyennesUE[$note['id_ue']]['total_notes'] += (float) $note['note'] * (float) $note['credits_ecue'];
            $moyennesUE[$note['id_ue']]['total_credits_ecue'] += (float) $note['credits_ecue'];
        }

        // Calculer la moyenne de chaque UE et la moyenne générale
        foreach ($moyennesUE as $id_ue => &$ue) {
            if ($ue['total_credits_ecue'] > 0) {
                $ue['moyenne'] = $ue['total_notes'] / $ue['total_credits_ecue'];
            }

            $totalPondere += $ue['moyenne'] * $ue['credits_ue'];
            $totalCredits += $ue['credits_ue'];

            // Condition de validation des crédits (ex: moyenne >= 10)
            if ($ue['moyenne'] >= 10.0) {
                $totalCreditsValides += $ue['credits_ue'];
            }
        }
        unset($ue); // Rompre la référence

        $moyenneGenerale = ($totalCredits > 0) ? $totalPondere / $totalCredits : 0;

        return [
            'moyenne_generale' => round($moyenneGenerale, 2),
            'credits_valides' => $totalCreditsValides,
            'details_ue' => $moyennesUE
        ];
    }







}