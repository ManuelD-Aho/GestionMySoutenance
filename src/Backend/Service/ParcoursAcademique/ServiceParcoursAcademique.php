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
    private ServiceSystemeInterface $systemeService;
    private ServiceSupervisionInterface $supervisionService;

    public function __construct(
        PDO $db,
        GenericModel $inscrireModel,
        GenericModel $evaluerModel,
        GenericModel $faireStageModel,
        GenericModel $penaliteModel,
        ServiceSystemeInterface $systemeService,
        ServiceSupervisionInterface $supervisionService
    ) {
        $this->db = $db;
        $this->inscrireModel = $inscrireModel;
        $this->evaluerModel = $evaluerModel;
        $this->faireStageModel = $faireStageModel;
        $this->penaliteModel = $penaliteModel;
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
}