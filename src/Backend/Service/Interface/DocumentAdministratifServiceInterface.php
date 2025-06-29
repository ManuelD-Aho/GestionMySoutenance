<?php

declare(strict_types=1);

namespace App\Backend\Service\Interface;

use App\Backend\Exception\ElementNonTrouveException;
use App\Backend\Exception\OperationImpossibleException;

interface DocumentAdministratifServiceInterface
{
    /**
     * Génère une attestation de scolarité en PDF pour un étudiant.
     *
     * @param string $numeroEtudiant Le numéro de l'étudiant.
     * @return string L'ID du document généré.
     * @throws ElementNonTrouveException Si l'étudiant ou son inscription n'est pas trouvé.
     */
    public function genererAttestationScolarite(string $numeroEtudiant): string;

    /**
     * Génère un bulletin de notes en PDF pour un étudiant et une année académique.
     *
     * @param string $numeroEtudiant Le numéro de l'étudiant.
     * @param string $idAnnee L'ID de l'année académique.
     * @return string L'ID du document généré.
     * @throws ElementNonTrouveException Si l'étudiant ou ses notes ne sont pas trouvées.
     */
    public function genererBulletinDeNotes(string $numeroEtudiant, string $idAnnee): string;

    /**
     * Génère un reçu de paiement en PDF pour une inscription.
     *
     * @param string $idInscription L'ID de l'inscription.
     * @return string L'ID du document généré.
     * @throws ElementNonTrouveException Si l'inscription n'est pas trouvée.
     * @throws OperationImpossibleException Si l'inscription n'a pas de paiement validé.
     */
    public function genererRecuDePaiement(string $idInscription): string;

    /**
     * Liste tous les documents administratifs officiels générés pour un étudiant.
     *
     * @param string $numeroEtudiant Le numéro de l'étudiant.
     * @return array La liste des documents.
     */
    public function listerDocumentsPourEtudiant(string $numeroEtudiant): array;

    /**
     * Archive un document administratif généré pour le masquer de la vue par défaut.
     *
     * @param string $idDocument L'ID du document.
     * @return bool True en cas de succès.
     */
    public function archiverDocument(string $idDocument): bool;
}