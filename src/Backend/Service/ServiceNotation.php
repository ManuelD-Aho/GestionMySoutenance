<?php

declare(strict_types=1);

namespace App\Backend\Service;

use PDO;
use App\Backend\Model\Evaluer;
use App\Backend\Model\Etudiant;
use App\Backend\Model\Ecue;
use App\Backend\Service\Interface\NotationServiceInterface;
use App\Backend\Service\Interface\AuditServiceInterface;
use App\Backend\Service\Interface\NotificationServiceInterface;
use App\Backend\Exception\ElementNonTrouveException;
use App\Backend\Exception\OperationImpossibleException;
use App\Backend\Exception\ValidationException;

class ServiceNotation implements NotationServiceInterface
{
    private PDO $pdo;
    private Evaluer $evaluerModel;
    private Etudiant $etudiantModel;
    private Ecue $ecueModel;
    private AuditServiceInterface $auditService;
    private NotificationServiceInterface $notificationService;

    public function __construct(
        PDO $pdo,
        Evaluer $evaluerModel,
        Etudiant $etudiantModel,
        Ecue $ecueModel,
        AuditServiceInterface $auditService,
        NotificationServiceInterface $notificationService
    ) {
        $this->pdo = $pdo;
        $this->evaluerModel = $evaluerModel;
        $this->etudiantModel = $etudiantModel;
        $this->ecueModel = $ecueModel;
        $this->auditService = $auditService;
        $this->notificationService = $notificationService;
    }

    public function saisirNote(string $numeroEtudiant, string $idEcue, float $note): bool
    {
        $this->validerEtudiantEtEcue($numeroEtudiant, $idEcue);

        $existingNote = $this->evaluerModel->trouverUnParCritere([
            'numero_carte_etudiant' => $numeroEtudiant,
            'id_ecue' => $idEcue
        ]);

        if ($existingNote) {
            return $this->modifierNote($numeroEtudiant, $idEcue, $note);
        }

        $donnees = [
            'numero_carte_etudiant' => $numeroEtudiant,
            'id_ecue' => $idEcue,
            'note_obtenue' => $note,
            'date_saisie' => (new \DateTime())->format('Y-m-d H:i:s')
        ];

        $resultat = (bool)$this->evaluerModel->creer($donnees);
        $this->auditService->enregistrerAction($_SESSION['user_id'], 'GRADE_ENTERED', null, 'Evaluer', $donnees);
        $this->notificationService->envoyerAUtilisateur($numeroEtudiant, 'NEW_GRADE_AVAILABLE_TPL', ['ecue_name' => $idEcue, 'grade' => $note]);

        return $resultat;
    }

    public function modifierNote(string $numeroEtudiant, string $idEcue, float $note): bool
    {
        $this->validerEtudiantEtEcue($numeroEtudiant, $idEcue);

        $noteExistante = $this->evaluerModel->trouverUnParCritere([
            'numero_carte_etudiant' => $numeroEtudiant,
            'id_ecue' => $idEcue
        ]);

        if (!$noteExistante) {
            throw new ElementNonTrouveException("Aucune note à modifier n'a été trouvée pour cet étudiant et cet ECUE.");
        }

        $donnees = ['note_obtenue' => $note];
        $resultat = $this->evaluerModel->mettreAJourParCles(
            ['numero_carte_etudiant' => $numeroEtudiant, 'id_ecue' => $idEcue],
            $donnees
        );

        $this->auditService->enregistrerAction($_SESSION['user_id'], 'GRADE_UPDATED', null, 'Evaluer', ['ancienne_note' => $noteExistante['note_obtenue'], 'nouvelle_note' => $note]);

        return $resultat;
    }

    public function importerNotesDepuisFichier(string $cheminFichier): array
    {
        // La logique de parsing de fichier (CSV, etc.) serait ici.
        // Pour la simulation, nous supposons que $lignes est un array de données parsées.
        $lignes = []; // Placeholder

        $rapport = ['succes' => 0, 'erreurs' => []];
        $this->pdo->beginTransaction();
        try {
            foreach ($lignes as $index => $ligne) {
                try {
                    $this->saisirNote($ligne['numero_etudiant'], $ligne['id_ecue'], (float)$ligne['note']);
                    $rapport['succes']++;
                } catch (\Exception $e) {
                    $rapport['erreurs'][] = "Ligne " . ($index + 1) . ": " . $e->getMessage();
                }
            }
            $this->pdo->commit();
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw new OperationImpossibleException("Erreur critique durant l'importation: " . $e->getMessage());
        }

        return $rapport;
    }

    public function calculerMoyenneSemestre(string $numeroEtudiant, string $idSemestre): float
    {
        $sql = "SELECT AVG(e.note_obtenue * ec.ponderation) / SUM(ec.ponderation) as moyenne
                FROM evaluer e
                JOIN ecue ec ON e.id_ecue = ec.id_ecue
                WHERE e.numero_carte_etudiant = :num_etudiant AND ec.id_semestre = :id_semestre";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':num_etudiant' => $numeroEtudiant, ':id_semestre' => $idSemestre]);
        $resultat = $stmt->fetch(PDO::FETCH_ASSOC);

        return (float)($resultat['moyenne'] ?? 0.0);
    }

    public function calculerMoyenneAnnee(string $numeroEtudiant, string $idAnnee): float
    {
        $sql = "SELECT AVG(e.note_obtenue * ec.ponderation) / SUM(ec.ponderation) as moyenne
                FROM evaluer e
                JOIN ecue ec ON e.id_ecue = ec.id_ecue
                JOIN inscrire i ON e.numero_carte_etudiant = i.numero_carte_etudiant
                WHERE e.numero_carte_etudiant = :num_etudiant AND i.id_annee_academique = :id_annee";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':num_etudiant' => $numeroEtudiant, ':id_annee' => $idAnnee]);
        $resultat = $stmt->fetch(PDO::FETCH_ASSOC);

        return (float)($resultat['moyenne'] ?? 0.0);
    }

    public function listerNotesParEtudiant(string $numeroEtudiant, ?string $idAnnee): array
    {
        $criteres = ['numero_carte_etudiant' => $numeroEtudiant];
        if ($idAnnee) {
            $criteres['id_annee_academique'] = $idAnnee;
        }
        return $this->evaluerModel->trouverParCritere($criteres);
    }

    private function validerEtudiantEtEcue(string $numeroEtudiant, string $idEcue): void
    {
        if (!$this->etudiantModel->trouverParIdentifiant($numeroEtudiant)) {
            throw new ElementNonTrouveException("L'étudiant avec le numéro '{$numeroEtudiant}' n'existe pas.");
        }
        if (!$this->ecueModel->trouverParIdentifiant($idEcue)) {
            throw new ElementNonTrouveException("L'ECUE avec l'ID '{$idEcue}' n'existe pas.");
        }
    }
}