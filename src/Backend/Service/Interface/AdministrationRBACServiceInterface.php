<?php

declare(strict_types=1);

namespace App\Backend\Service\Interface;

use App\Backend\Exception\DoublonException;
use App\Backend\Exception\ElementNonTrouveException;

interface AdministrationRBACServiceInterface
{
    /**
     * Crée un nouveau groupe d'utilisateurs (rôle).
     *
     * @param string $idGroupe L'ID souhaité pour le groupe (ex: GRP_NOUVEAU_ROLE).
     * @param string $libelle Le nom lisible du groupe.
     * @return string L'ID du groupe créé.
     * @throws DoublonException Si un groupe avec le même ID ou libellé existe déjà.
     */
    public function creerGroupe(string $idGroupe, string $libelle): string;

    /**
     * Crée un nouveau traitement (permission), potentiellement rattaché à un parent.
     *
     * @param string $idTraitement L'ID du traitement (ex: TRAIT_MODULE_ACTION).
     * @param string $libelle Le nom lisible du traitement.
     * @param string|null $idParentTraitement L'ID du traitement parent.
     * @param string|null $iconeClass Classe CSS pour l'icône du menu.
     * @param string|null $urlAssociee URL de la page associée.
     * @return string L'ID du traitement créé.
     * @throws DoublonException Si un traitement avec le même ID existe déjà.
     */
    public function creerTraitement(string $idTraitement, string $libelle, ?string $idParentTraitement, ?string $iconeClass, ?string $urlAssociee): string;

    /**
     * Attribue un traitement à un groupe. Opération transactionnelle.
     *
     * @param string $idTraitement
     * @param string $idGroupe
     * @return bool
     */
    public function assignerTraitementAGroupe(string $idTraitement, string $idGroupe): bool;

    /**
     * Retire un traitement d'un groupe. Opération transactionnelle.
     *
     * @param string $idTraitement
     * @param string $idGroupe
     * @return bool
     */
    public function retirerTraitementDeGroupe(string $idTraitement, string $idGroupe): bool;

    /**
     * Récupère la liste complète des traitements (permissions) associés à un groupe.
     *
     * @param string $idGroupe
     * @return array
     */
    public function getTraitementsPourGroupe(string $idGroupe): array;
}