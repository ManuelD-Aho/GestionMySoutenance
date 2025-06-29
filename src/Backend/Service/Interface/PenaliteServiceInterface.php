<?php

declare(strict_types=1);

namespace App\Backend\Service\Interface;

use App\Backend\Exception\ElementNonTrouveException;
use App\Backend\Exception\OperationImpossibleException;

interface PenaliteServiceInterface
{
    /**
     * Tâche CRON qui détecte automatiquement les étudiants en retard et crée les pénalités associées.
     *
     * @return int Le nombre de pénalités créées.
     */
    public function detecterEtCreerPenalites(): int;

    /**
     * Applique une pénalité manuellement à un étudiant.
     *
     * @param string $numeroEtudiant Le numéro de l'étudiant.
     * @param string $motif Le motif de la pénalité.
     * @param float|null $montant Le montant, si applicable.
     * @return string L'ID de la nouvelle pénalité.
     */
    public function appliquerPenaliteManuellement(string $numeroEtudiant, string $motif, ?float $montant): string;

    /**
     * Marque une pénalité comme étant réglée.
     *
     * @param string $idPenalite L'ID de la pénalité.
     * @param string $idAgent L'ID de l'agent qui régularise.
     * @return bool True en cas de succès.
     * @throws ElementNonTrouveException Si la pénalité n'existe pas.
     */
    public function regulariserPenalite(string $idPenalite, string $idAgent): bool;

    /**
     * Liste les pénalités avec des filtres.
     *
     * @param array $filtres Critères de filtrage (ex: statut, étudiant).
     * @return array La liste des pénalités.
     */
    public function listerPenalites(array $filtres = []): array;

    /**
     * Récupère toutes les pénalités (réglées ou non) d'un étudiant.
     *
     * @param string $numeroEtudiant Le numéro de l'étudiant.
     * @return array La liste de ses pénalités.
     */
    public function getPenalitesPourEtudiant(string $numeroEtudiant): array;

    /**
     * Annule une pénalité qui a été appliquée par erreur.
     *
     * @param string $idPenalite L'ID de la pénalité.
     * @param string $motif Le motif de l'annulation.
     * @return bool True en cas de succès.
     * @throws OperationImpossibleException Si la pénalité est déjà réglée.
     */
    public function annulerPenalite(string $idPenalite, string $motif): bool;
}