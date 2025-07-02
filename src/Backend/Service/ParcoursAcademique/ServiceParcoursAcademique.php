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
        $this->supervisionService->enregistrerAction($_SESSION['user_id'] ?? 'SYSTEM', 'VALIDATION_STAGE', $numeroEtudiant, 'Etudiant', ['entreprise' => $idEntreprise]);
        return true;
    }

    public function listerStages(array $filtres = []): array {
        return $this->faireStageModel->trouverParCritere($filtres);
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

        $derniereInscription = $this->inscrireModel->trouverUnParCritere(
            ['numero_carte_etudiant' => $numeroEtudiant, 'id_annee_academique' => $anneeActive['id_annee_academique']],
            ['*'],
            'AND',
            'date_inscription DESC'
        );

        if (!$derniereInscription || $derniereInscription['id_statut_paiement'] !== 'PAIE_OK') {
            return false;
        }

        $stage = $this->faireStageModel->trouverUnParCritere(['numero_carte_etudiant' => $numeroEtudiant]);
        if (!$stage) {
            return false;
        }

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
            $this->inscrireModel->mettreAJourParCles(
                ['numero_carte_etudiant' => $numeroEtudiant, 'id_niveau_etude' => $inscription['id_niveau_etude'], 'id_annee_academique' => $idAnnee],
                ['id_decision_passage' => $idDecision]
            );

            if ($idDecision === 'DEC_REDOUBLANT') {
                $anneeActuelle = (int) substr($idAnnee, 6, 4);
                $anneeSuivante = $anneeActuelle + 1;
                $idAnneeSuivante = "ANNEE-{$anneeActuelle}-{$anneeSuivante}";

                $this->creerInscription([
                    'numero_carte_etudiant' => $numeroEtudiant,
                    'id_niveau_etude' => $inscription['id_niveau_etude'],
                    'id_annee_academique' => $idAnneeSuivante,
                    'montant_inscription' => $inscription['montant_inscription'],
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

        foreach ($moyennesUE as $id_ue => &$ue) {
            if ($ue['total_credits_ecue'] > 0) {
                $ue['moyenne'] = $ue['total_notes'] / $ue['total_credits_ecue'];
            }

            $totalPondere += $ue['moyenne'] * $ue['credits_ue'];
            $totalCredits += $ue['credits_ue'];

            if ($ue['moyenne'] >= 10.0) {
                $totalCreditsValides += $ue['credits_ue'];
            }
        }
        unset($ue);

        $moyenneGenerale = ($totalCredits > 0) ? $totalPondere / $totalCredits : 0;

        return [
            'moyenne_generale' => round($moyenneGenerale, 2),
            'credits_valides' => $totalCreditsValides,
            'details_ue' => $moyennesUE
        ];
    }
}