<?php

namespace App\Backend\Service\IdentifiantGenerator;

use PDO;
use App\Backend\Model\Sequences;
use App\Backend\Model\AnneeAcademique;
use App\Backend\Service\SupervisionAdmin\ServiceSupervisionAdminInterface;
use App\Backend\Exception\OperationImpossibleException;
use App\Backend\Exception\ElementNonTrouveException;

class IdentifiantGenerator implements IdentifiantGeneratorInterface
{
    private Sequences $sequencesModel;
    private AnneeAcademique $anneeAcademiqueModel;
    private ServiceSupervisionAdminInterface $supervisionService;
    private PDO $db;

    public function __construct(
        PDO $db,
        Sequences $sequencesModel,
        AnneeAcademique $anneeAcademiqueModel,
        ServiceSupervisionAdminInterface $supervisionService
    ) {
        $this->db = $db;
        $this->sequencesModel = $sequencesModel;
        $this->anneeAcademiqueModel = $anneeAcademiqueModel;
        $this->supervisionService = $supervisionService;
    }

    public function genererIdentifiantUnique(string $prefixe, ?int $annee = null): string
    {
        if ($annee === null) {
            $anneeActive = $this->anneeAcademiqueModel->trouverUnParCritere(['est_active' => 1]);
            if (!$anneeActive || !isset($anneeActive['libelle_annee_academique'])) {
                throw new ElementNonTrouveException("Aucune année académique active trouvée pour la génération d'identifiant.");
            }
            $annee = (int) substr($anneeActive['libelle_annee_academique'], 0, 4);
        }

        try {
            $stmt = $this->db->prepare("SELECT `valeur_actuelle` FROM `sequences` WHERE `nom_sequence` = :prefixe AND `annee` = :annee FOR UPDATE");
            $stmt->bindParam(':prefixe', $prefixe);
            $stmt->bindParam(':annee', $annee);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            $nextSequence = 1;
            if ($result) {
                $nextSequence = $result['valeur_actuelle'] + 1;
                $updateStmt = $this->db->prepare("UPDATE `sequences` SET `valeur_actuelle` = :valeur WHERE `nom_sequence` = :prefixe AND `annee` = :annee");
                $updateStmt->bindParam(':valeur', $nextSequence);
                $updateStmt->bindParam(':prefixe', $prefixe);
                $updateStmt->bindParam(':annee', $annee);
                $updateStmt->execute();
            } else {
                $insertStmt = $this->db->prepare("INSERT INTO `sequences` (`nom_sequence`, `annee`, `valeur_actuelle`) VALUES (:prefixe, :annee, 1)");
                $insertStmt->bindParam(':prefixe', $prefixe);
                $insertStmt->bindParam(':annee', $annee);
                $insertStmt->execute();
            }

            $formattedSequence = str_pad((string)$nextSequence, 4, '0', STR_PAD_LEFT);
            $identifiant = "{$prefixe}-{$annee}-{$formattedSequence}";

            $this->supervisionService->enregistrerAction(
                $_SESSION['user_id'] ?? 'SYSTEM',
                'GENERATION_ID_UNIQUE',
                "Identifiant unique '{$identifiant}' généré avec le préfixe '{$prefixe}' pour l'année '{$annee}'.",
                $identifiant,
                'ID_GENERATED'
            );

            return $identifiant;

        } catch (\PDOException $e) {
            $this->supervisionService->enregistrerAction(
                $_SESSION['user_id'] ?? 'SYSTEM',
                'ECHEC_GENERATION_ID_UNIQUE',
                "Erreur génération ID pour préfixe '{$prefixe}': " . $e->getMessage()
            );
            throw new OperationImpossibleException("Échec de la génération d'identifiant unique : " . $e->getMessage(), 0, $e);
        }
    }
}