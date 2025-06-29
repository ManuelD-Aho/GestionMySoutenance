<?php

declare(strict_types=1);

namespace App\Backend\Service\Interface;

use App\Backend\Exception\DoublonException;
use App\Backend\Exception\ElementNonTrouveException;
use App\Backend\Exception\OperationImpossibleException;

interface InscriptionServiceInterface
{
    /**
     * Crée une nouvelle inscription administrative pour un étudiant.
     *
     * @param array $donnees Données de l'inscription (numero_etudiant, id_niveau_etude, etc.).
     * @return string L'ID de la nouvelle inscription.
     * @throws DoublonException Si l'étudiant est déjà inscrit pour ce niveau et cette année.
     * @throws OperationImpossibleException Si l'étudiant n'est pas éligible.
     */
    public function creerInscription(array $donnees): string;

    /**
     * Met à jour une inscription existante.
     *
     * @param string $id L'ID de l'inscription.
     * @param array $donnees Les données à modifier.
     * @return bool True en cas de succès.
     * @throws ElementNonTrouveException Si l'inscription n'existe pas.
     */
    public function mettreAJourInscription(string $id, array $donnees): bool;

    /**
     * Supprime une inscription.
     *
     * @param string $id L'ID de l'inscription.
     * @return bool True en cas de succès.
     */
    public function supprimerInscription(string $id): bool;

    /**
     * Récupère les détails d'une inscription par son ID.
     *
     * @param string $id L'ID de l'inscription.
     * @return array|null Les données de l'inscription ou null.
     */
    public function recupererInscriptionParId(string $id): ?array;

    /**
     * Liste les inscriptions avec des filtres.
     *
     * @param array $filtres Critères de filtrage (ex: statut_paiement, année).
     * @return array La liste des inscriptions.
     */
    public function listerInscriptions(array $filtres = []): array;

    /**
     * Valide le paiement d'une inscription.
     *
     * @param string $id L'ID de l'inscription.
     * @param array $detailsPaiement Détails de la transaction.
     * @return bool True en cas de succès.
     */
    public function validerPaiementInscription(string $id, array $detailsPaiement): bool;

    /**
     * Vérifie si un étudiant remplit les conditions pour s'inscrire à un niveau d'étude.
     *
     * @param string $numeroEtudiant Le numéro de l'étudiant.
     * @param string $idNiveauEtude L'ID du niveau d'étude visé.
     * @return bool True si l'étudiant est éligible.
     */
    public function verifierEligibiliteInscription(string $numeroEtudiant, string $idNiveauEtude): bool;
}