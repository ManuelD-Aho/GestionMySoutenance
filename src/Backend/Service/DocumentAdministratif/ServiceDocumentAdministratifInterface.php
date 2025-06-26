<?php

namespace App\Backend\Service\DocumentAdministratif;

interface ServiceDocumentAdministratifInterface
{
    /**
     * Génère une attestation de scolarité au format PDF.
     * @param string $numeroEtudiant Le numéro de carte de l'étudiant.
     * @param string $idAnneeAcademique L'ID de l'année académique pour laquelle l'attestation est générée.
     * @return string Le chemin relatif du fichier PDF généré.
     * @throws \Exception En cas d'erreur.
     */
    public function genererAttestationScolarite(string $numeroEtudiant, string $idAnneeAcademique): string;

    /**
     * Génère un bulletin de notes pour un étudiant pour une année académique donnée.
     * @param string $numeroEtudiant Le numéro de carte de l'étudiant.
     * @param string $idAnneeAcademique L'ID de l'année académique.
     * @return string Le chemin relatif du fichier PDF généré.
     * @throws \Exception En cas d'erreur.
     */
    public function genererBulletinNotes(string $numeroEtudiant, string $idAnneeAcademique): string;

    /**
     * Génère un reçu de paiement pour une inscription donnée.
     * @param string $idInscription L'ID de l'inscription (clé composite encodée ou ID unique si la table a été modifiée).
     * @return string Le chemin relatif du fichier PDF généré.
     * @throws \Exception En cas d'erreur.
     */
    public function genererRecuPaiement(string $idInscription): string;

    /**
     * Liste les documents générés par un membre du personnel administratif.
     * @param string $numeroPersonnel Le numéro du personnel.
     * @return array Liste des documents.
     */
    public function listerDocumentsGeneresParPersonnel(string $numeroPersonnel): array;
}