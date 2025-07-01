<?php
// src/Backend/Service/Systeme/ServiceSystemeInterface.php

namespace App\Backend\Service\Systeme;

interface ServiceSystemeInterface
{
    /**
     * Génère un identifiant unique et sémantique pour une entité.
     * Format : PREFIXE-ANNEE-SEQUENCE (ex: RAP-2024-0001).
     *
     * @param string $prefixe Le préfixe de l'entité (ex: 'ETU', 'RAP', 'PV').
     * @return string L'identifiant unique généré.
     */
    public function genererIdentifiantUnique(string $prefixe): string;

    /**
     * Récupère la valeur d'un paramètre système spécifique.
     *
     * @param string $cle La clé du paramètre (ex: 'MAX_LOGIN_ATTEMPTS').
     * @param mixed $defaut La valeur à retourner si la clé n'est pas trouvée.
     * @return mixed La valeur du paramètre.
     */
    public function getParametre(string $cle, mixed $defaut = null);

    /**
     * Récupère l'ensemble des paramètres système sous forme de tableau associatif.
     *
     * @return array Un tableau [clé => valeur].
     */
    public function getAllParametres(): array;

    /**
     * Met à jour un ou plusieurs paramètres système.
     *
     * @param array $parametres Tableau associatif [clé => valeur] des paramètres à mettre à jour.
     * @return bool True en cas de succès.
     */
    public function setParametres(array $parametres): bool;

    /**
     * Récupère l'année académique actuellement active.
     *
     * @return array|null Les données de l'année académique active ou null si aucune n'est définie.
     */
    public function getAnneeAcademiqueActive(): ?array;

    /**
     * Définit une année académique comme étant l'année active.
     * Désactive automatiquement l'ancienne année active.
     *
     * @param string $idAnneeAcademique L'ID de l'année à activer.
     * @return bool True en cas de succès.
     */
    public function setAnneeAcademiqueActive(string $idAnneeAcademique): bool;

    /**
     * Gère le CRUD pour n'importe quelle table de référentiel simple.
     *
     * @param string $operation L'opération à effectuer ('create', 'read', 'update', 'delete', 'list').
     * @param string $nomReferentiel Le nom de la table du référentiel (ex: 'grade').
     * @param string|null $id L'ID de l'enregistrement pour les opérations 'read', 'update', 'delete'.
     * @param array|null $donnees Les données pour les opérations 'create', 'update'.
     * @return mixed Le résultat de l'opération (bool, array, ou null).
     */
    public function gererReferentiel(string $operation, string $nomReferentiel, ?string $id = null, ?array $donnees = null);
}