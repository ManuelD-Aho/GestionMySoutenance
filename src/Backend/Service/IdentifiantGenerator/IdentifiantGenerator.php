<?php
namespace App\Backend\Service\IdentifiantGenerator;

use PDO;
use App\Backend\Model\Sequences; // Le modèle pour la table 'sequences'
use App\Backend\Model\AnneeAcademique; // Pour récupérer l'année académique active
use App\Backend\Service\SupervisionAdmin\ServiceSupervisionAdmin; // Pour journalisation
use App\Backend\Exception\OperationImpossibleException;
use App\Backend\Exception\ElementNonTrouveException;

class IdentifiantGenerator implements IdentifiantGeneratorInterface
{
    private Sequences $sequencesModel;
    private AnneeAcademique $anneeAcademiqueModel;
    private ServiceSupervisionAdmin $supervisionService;
    private PDO $db; // Injecter PDO directement pour gérer les verrous manuellement si nécessaire

    public function __construct(PDO $db, ServiceSupervisionAdmin $supervisionService)
    {
        $this->db = $db; // Utilisation directe de PDO pour un contrôle fin des transactions/verrous
        $this->sequencesModel = new Sequences($db);
        $this->anneeAcademiqueModel = new AnneeAcademique($db);
        $this->supervisionService = $supervisionService;
    }

    /**
     * Génère un identifiant unique formaté pour une entité donnée (PREFIXE-ANNEE-SEQUENCE).
     *
     * @param string $prefixe Le préfixe de l'identifiant (ex: 'RAP', 'ETU', 'PV').
     * @param int|null $annee Optionnel: l'année pour laquelle générer l'identifiant. Par défaut, l'année académique active.
     * @return string L'identifiant unique généré.
     * @throws OperationImpossibleException En cas d'échec de la génération de l'identifiant.
     * @throws ElementNonTrouveException Si aucune année académique active n'est trouvée.
     */
    public function genererIdentifiantUnique(string $prefixe, ?int $annee = null): string
    {
        // Déterminer l'année académique
        if ($annee === null) {
            $anneeActive = $this->anneeAcademiqueModel->trouverUnParCritere(['est_active' => 1]);
            if (!$anneeActive || !isset($anneeActive['libelle_annee_academique'])) {
                throw new ElementNonTrouveException("Aucune année académique active trouvée pour la génération d'identifiant.");
            }
            // Extraire l'année numérique (ex: "2024" de "2024-2025")
            $annee = (int) substr($anneeActive['libelle_annee_academique'], 0, 4);
        }

        $currentYear = (int) date('Y');
        // Si l'année académique active est par exemple "2024-2025", l'année courante est "2025" au deuxième semestre.
        // Il faut s'assurer que l'année utilisée pour l'ID est l'année de DÉBUT de l'année académique.
        // Si $anneeActive['libelle_annee_academique'] est "2024-2025", alors $annee devrait être 2024.
        // Ajustement : si $anneeActive est une entité, elle devrait avoir une colonne `annee_debut` ou similaire.
        // Pour l'instant, on se base sur le format "AAAA-AAAA" et on prend le début.
        if ($anneeActive && isset($anneeActive['libelle_annee_academique'])) {
            $anneePourID = (int) substr($anneeActive['libelle_annee_academique'], 0, 4);
        } else {
            $anneePourID = $currentYear; // Fallback si pas d'année académique active définie
        }


        // Assurer l'atomicité de la génération de séquence
        $this->db->beginTransaction();
        try {
            // Verrouiller la ligne de la séquence pour éviter les conflits concurrentiels
            // SELECT FOR UPDATE bloque la ligne tant que la transaction n'est pas commitée/rollbackée
            $stmt = $this->db->prepare("SELECT `valeur_actuelle` FROM `sequences` WHERE `nom_sequence` = :prefixe AND `annee` = :annee FOR UPDATE");
            $stmt->bindParam(':prefixe', $prefixe);
            $stmt->bindParam(':annee', $anneeForID);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            $nextSequence = 1;
            if ($result) {
                // La séquence existe pour cette année, incrémenter
                $nextSequence = $result['valeur_actuelle'] + 1;
                $updateStmt = $this->db->prepare("UPDATE `sequences` SET `valeur_actuelle` = :valeur WHERE `nom_sequence` = :prefixe AND `annee` = :annee");
                $updateStmt->bindParam(':valeur', $nextSequence);
                $updateStmt->bindParam(':prefixe', $prefixe);
                $updateStmt->bindParam(':annee', $anneeForID);
                $updateStmt->execute();
            } else {
                // La séquence n'existe pas pour cette année, la créer
                $insertStmt = $this->db->prepare("INSERT INTO `sequences` (`nom_sequence`, `annee`, `valeur_actuelle`) VALUES (:prefixe, :annee, 1)");
                $insertStmt->bindParam(':prefixe', $prefixe);
                $insertStmt->bindParam(':annee', $anneeForID);
                $insertStmt->execute();
            }

            $this->db->commit(); // Valider la transaction et libérer le verrou

            // Formater l'identifiant (ex: ETU-2025-0001)
            $formattedSequence = str_pad($nextSequence, 4, '0', STR_PAD_LEFT);
            $identifiant = "{$prefixe}-{$anneeForID}-{$formattedSequence}";

            $this->supervisionService->enregistrerAction(
                $_SESSION['user_id'] ?? 'SYSTEM',
                'GENERATION_ID_UNIQUE',
                "Identifiant unique '{$identifiant}' généré avec le préfixe '{$prefixe}' pour l'année '{$anneeForID}'.",
                $identifiant,
                'ID_GENERATED' // Ou le type d'entité spécifique (ex: 'Utilisateur', 'RapportEtudiant')
            );

            return $identifiant;

        } catch (\PDOException $e) {
            $this->db->rollBack(); // Annuler la transaction en cas d'erreur
            $this->supervisionService->enregistrerAction(
                $_SESSION['user_id'] ?? 'SYSTEM',
                'ECHEC_GENERATION_ID_UNIQUE',
                "Erreur génération ID pour préfixe '{$prefixe}': " . $e->getMessage()
            );
            throw new OperationImpossibleException("Échec de la génération d'identifiant unique : " . $e->getMessage(), 0, $e);
        }
    }
}