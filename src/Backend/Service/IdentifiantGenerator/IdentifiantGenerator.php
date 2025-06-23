<?php

namespace App\Backend\Service\IdentifiantGenerator;

use PDO;
use App\Backend\Model\AnneeAcademique;
use App\Backend\Exception\OperationImpossibleException;
use App\Backend\Exception\ElementNonTrouveException;

/**
 * Service de génération d'identifiants uniques, transactionnel et basé sur des stratégies.
 * Utilise une table 'sequences' et un fichier de configuration pour une flexibilité maximale.
 */
class IdentifiantGenerator implements IdentifiantGeneratorInterface
{
    private PDO $db;
    private array $strategies;
    private AnneeAcademique $anneeAcademiqueModel;

    public function __construct(PDO $db)
    {
        $this->db = $db;
        $this->anneeAcademiqueModel = new AnneeAcademique($db);

        $configFile = __DIR__ . '/../../../Config/sequences.php';
        if (!file_exists($configFile)) {
            throw new OperationImpossibleException("Le fichier de configuration des séquences est introuvable.");
        }
        $this->strategies = require $configFile;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(string $entityAlias, array $context = []): string
    {
        if (!isset($this->strategies[$entityAlias])) {
            throw new OperationImpossibleException("Aucune stratégie de génération d'ID n'est définie pour '{$entityAlias}'.");
        }

        $strategy = $this->strategies[$entityAlias];
        $prefix = $strategy['prefix'];
        $padding = $strategy['padding'];
        $resetYearly = $strategy['reset_yearly'];
        $sequenceName = $prefix; // Le nom de la séquence en BDD est le préfixe lui-même.

        $year = 0; // Année 0 pour les séquences globales non-annuelles.
        if ($resetYearly) {
            if (isset($context['annee'])) {
                $year = (int) $context['annee'];
            } else {
                $anneeActive = $this->anneeAcademiqueModel->trouverUnParCritere(['est_active' => 1]);
                if (!$anneeActive) {
                    throw new ElementNonTrouveException("Impossible de générer un ID annuel : aucune année académique n'est active.");
                }
                // Extrait l'année de début du libellé (ex: 2024 de "2024-2025")
                $year = (int) substr($anneeActive['libelle_annee_academique'], 0, 4);
            }
        }

        $this->db->beginTransaction();
        try {
            // Verrouillage pessimiste de la ligne pour garantir l'atomicité et éviter les race conditions.
            $stmt = $this->db->prepare(
                "SELECT valeur_actuelle FROM sequences WHERE nom_sequence = :name AND annee = :year FOR UPDATE"
            );
            $stmt->execute([':name' => $sequenceName, ':year' => $year]);
            $sequence = $stmt->fetch(PDO::FETCH_ASSOC);

            $nextValue = 1;
            if ($sequence) {
                // La séquence existe, on l'incrémente.
                $nextValue = $sequence['valeur_actuelle'] + 1;
                $updateStmt = $this->db->prepare(
                    "UPDATE sequences SET valeur_actuelle = :value WHERE nom_sequence = :name AND annee = :year"
                );
                $updateStmt->execute([':value' => $nextValue, ':name' => $sequenceName, ':year' => $year]);
            } else {
                // C'est le premier ID pour cette séquence/année, on crée la ligne.
                $insertStmt = $this->db->prepare(
                    "INSERT INTO sequences (nom_sequence, annee, valeur_actuelle) VALUES (:name, :year, :value)"
                );
                $insertStmt->execute([':name' => $sequenceName, ':year' => $year, ':value' => $nextValue]);
            }

            $this->db->commit();

            // Formatage final de l'identifiant
            $paddedSequence = str_pad($nextValue, $padding, '0', STR_PAD_LEFT);

            if ($resetYearly) {
                return "{$prefix}-{$year}-{$paddedSequence}";
            } else {
                return "{$prefix}-{$paddedSequence}";
            }

        } catch (\Exception $e) {
            $this->db->rollBack();
            // Log l'erreur pour le débogage
            error_log("ID Generation Failed for '{$entityAlias}': " . $e->getMessage());
            // Lancer une exception claire pour la couche de service appelante
            throw new OperationImpossibleException("Échec de la génération de l'identifiant pour '{$entityAlias}'.", 0, $e);
        }
    }
}