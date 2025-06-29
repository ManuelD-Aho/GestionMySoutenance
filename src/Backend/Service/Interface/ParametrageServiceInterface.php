<?php

declare(strict_types=1);

namespace App\Backend\Service\Interface;

interface ParametrageServiceInterface
{
    /**
     * Récupère la valeur d'un paramètre système par sa clé.
     *
     * @param string $cle La clé du paramètre.
     * @return string|null La valeur du paramètre ou null si non trouvé.
     */
    public function getParametre(string $cle): ?string;

    /**
     * Définit la valeur d'un paramètre système.
     *
     * @param string $cle La clé du paramètre.
     * @param string $valeur La nouvelle valeur.
     * @return bool True en cas de succès.
     */
    public function setParametre(string $cle, string $valeur): bool;

    /**
     * Récupère tous les paramètres du système.
     *
     * @return array Un tableau associatif de tous les paramètres.
     */
    public function getAllParametres(): array;

    /**
     * Récupère les paramètres appartenant à une catégorie spécifique.
     *
     * @param string $categorie La catégorie de paramètres.
     * @return array La liste des paramètres de cette catégorie.
     */
    public function getParametresParCategorie(string $categorie): array;

    /**
     * Réinitialise tous les paramètres à leur valeur par défaut.
     *
     * @return bool True en cas de succès.
     */
    public function reinitialiserParametresParDefaut(): bool;
}