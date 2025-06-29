<?php

declare(strict_types=1);

namespace App\Backend\Service\Interface;

use App\Backend\Exception\DoublonException;
use App\Backend\Exception\ElementNonTrouveException;
use App\Backend\Exception\OperationImpossibleException;

interface ReferentielServiceInterface
{
    /**
     * Crée un nouvel item dans une table de référence.
     *
     * @param string $nomReferentiel Le nom de la table de référence (ex: 'grade', 'statut_rapport_ref').
     * @param array $donnees Les données de l'item.
     * @return string L'ID du nouvel item.
     * @throws DoublonException Si un item avec le même ID ou libellé existe déjà.
     */
    public function creerItem(string $nomReferentiel, array $donnees): string;

    /**
     * Met à jour un item dans une table de référence.
     *
     * @param string $nomReferentiel Le nom de la table.
     * @param string $id L'ID de l'item.
     * @param array $donnees Les données à modifier.
     * @return bool True en cas de succès.
     * @throws ElementNonTrouveException Si l'item n'existe pas.
     */
    public function mettreAJourItem(string $nomReferentiel, string $id, array $donnees): bool;

    /**
     * Supprime un item d'une table de référence.
     *
     * @param string $nomReferentiel Le nom de la table.
     * @param string $id L'ID de l'item.
     * @return bool True en cas de succès.
     * @throws OperationImpossibleException Si l'item est utilisé par d'autres entités.
     */
    public function supprimerItem(string $nomReferentiel, string $id): bool;

    /**
     * Liste tous les items d'un référentiel.
     *
     * @param string $nomReferentiel Le nom de la table.
     * @return array La liste des items.
     */
    public function listerItems(string $nomReferentiel): array;

    /**
     * Récupère un item spécifique par son ID.
     *
     * @param string $nomReferentiel Le nom de la table.
     * @param string $id L'ID de l'item.
     * @return array|null Les données de l'item ou null.
     */
    public function getItemParId(string $nomReferentiel, string $id): ?array;
}