<?php

namespace App\Backend\Service\DocumentGenerator;

use App\Backend\Model\DocumentGenere;

interface ServiceDocumentGeneratorInterface
{
    /**
     * Retourne le modèle DocumentGenere pour des opérations externes si nécessaire.
     * @return DocumentGenere
     */
    public function getDocumentGenereModel(): DocumentGenere;

    /**
     * Génère un Procès-Verbal de validation au format PDF.
     * @param string $idCompteRendu L'ID du PV à générer.
     * @return string Le chemin relatif vers le fichier PDF généré.
     * @throws \Exception En cas d'erreur.
     */
    public function genererPvValidation(string $idCompteRendu): string;

    /**
     * Génère une attestation de scolarité au format PDF.
     * @param string $numeroEtudiant Le numéro de carte de l'étudiant.
     * @param string $idAnneeAcademique L'ID de l'année académique.
     * @return string Le chemin relatif vers le fichier PDF généré.
     * @throws \Exception En cas d'erreur.
     */
    public function genererAttestationScolarite(string $numeroEtudiant, string $idAnneeAcademique): string;

    /**
     * Génère un bulletin de notes pour un étudiant pour une année académique donnée.
     * @param string $numeroEtudiant Le numéro de carte de l'étudiant.
     * @param string $idAnneeAcademique L'ID de l'année académique.
     * @return string Le chemin relatif vers le fichier PDF généré.
     * @throws \Exception En cas d'erreur.
     */
    public function genererBulletinNotes(string $numeroEtudiant, string $idAnneeAcademique): string;

    /**
     * Génère un reçu de paiement pour une inscription donnée.
     * @param array $inscription Les données de l'inscription.
     * @return string Le chemin relatif vers le fichier PDF généré.
     * @throws \Exception En cas d'erreur.
     */
    public function genererRecuPaiement(array $inscription): string;
}