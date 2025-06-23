<?php

namespace App\Backend\Service\IdentifiantGenerator;

use App\Backend\Exception\OperationImpossibleException;

/**
 * Interface pour le service de génération d'identifiants uniques et métier.
 * Définit la méthode principale pour générer un ID basé sur une stratégie.
 */
interface IdentifiantGeneratorInterface
{
    /**
     * Génère un identifiant unique basé sur une stratégie prédéfinie dans la configuration.
     * Le format peut être annuel (PREFIXE-ANNEE-SEQUENCE) ou global (PREFIXE-SEQUENCE).
     *
     * @param string $entityAlias L'alias de l'entité (ex: 'rapport_etudiant', 'compte_rendu').
     * @param array $context Données contextuelles optionnelles, comme ['annee' => 2024].
     * @return string L'identifiant unique généré.
     * @throws OperationImpossibleException Si la stratégie n'est pas trouvée ou en cas d'erreur de base de données.
     */
    public function generate(string $entityAlias, array $context = []): string;
}