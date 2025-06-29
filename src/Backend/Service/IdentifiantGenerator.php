<?php

declare(strict_types=1);

namespace App\Backend\Service;

use PDO;
use App\Backend\Model\AnneeAcademique;
use App\Backend\Service\Interface\IdentifiantGeneratorInterface;
use App\Backend\Exception\OperationImpossibleException;

/**
 * Service de génération d'identifiants uniques, annuels et séquentiels.
 * Conçu pour être robuste en environnement de production avec gestion des transactions
 * et des verrous pour garantir l'unicité même sous forte concurrence.
 */
class IdentifiantGenerator implements IdentifiantGeneratorInterface
{
    private PDO $pdo;
    private AnneeAcademique $anneeAcademiqueModel;

    public function __construct(PDO $pdo, AnneeAcademique $anneeAcademiqueModel)
    {
        $this->pdo = $pdo;
        $this->anneeAcademiqueModel = $anneeAcademiqueModel;
    }

    /**
     * @inheritdoc
     */
    public function generer(string $prefixe): string
    {
        $anneeActive = $this->anneeAcademiqueModel->trouverUnParCritere(['est_active' => true]);
        if (!$anneeActive) {
            throw new OperationImpossibleException("Aucune année académique active n'est définie pour générer un identifiant.");
        }
        $anneeCourante = (int)explode('-', $anneeActive['libelle_annee_academique'])[0];
        $nomSequence = strtoupper($prefixe);

        $this->pdo->beginTransaction();

        try {
            // Verrouillage pessimiste de la ligne pour éviter les race conditions.
            // Le worker suivant attendra la fin de la transaction.
            $sqlSelect = "SELECT valeur_actuelle FROM sequences WHERE nom_sequence = :nom_sequence AND annee = :annee FOR UPDATE";
            $stmtSelect = $this->pdo->prepare($sqlSelect);
            $stmtSelect->execute([':nom_sequence' => $nomSequence, ':annee' => $anneeCourante]);
            $sequence = $stmtSelect->fetch(PDO::FETCH_ASSOC);

            if ($sequence) {
                // La séquence existe, on l'incrémente.
                $nouvelleValeur = (int)$sequence['valeur_actuelle'] + 1;
                $sqlUpdate = "UPDATE sequences SET valeur_actuelle = :valeur WHERE nom_sequence = :nom_sequence AND annee = :annee";
                $stmtUpdate = $this->pdo->prepare($sqlUpdate);
                $stmtUpdate->execute([
                    ':valeur' => $nouvelleValeur,
                    ':nom_sequence' => $nomSequence,
                    ':annee' => $anneeCourante
                ]);
            } else {
                // La séquence n'existe pas pour cette année, on la crée.
                $nouvelleValeur = 1;
                $sqlInsert = "INSERT INTO sequences (nom_sequence, annee, valeur_actuelle) VALUES (:nom_sequence, :annee, :valeur)";
                $stmtInsert = $this->pdo->prepare($sqlInsert);
                $stmtInsert->execute([
                    ':nom_sequence' => $nomSequence,
                    ':annee' => $anneeCourante,
                    ':valeur' => $nouvelleValeur
                ]);
            }

            $this->pdo->commit();

            return sprintf('%s-%d-%04d', $nomSequence, $anneeCourante, $nouvelleValeur);

        } catch (\Exception $e) {
            $this->pdo->rollBack();
            // Log l'erreur $e
            throw new OperationImpossibleException("Échec critique de la génération de l'identifiant : " . $e->getMessage());
        }
    }
}