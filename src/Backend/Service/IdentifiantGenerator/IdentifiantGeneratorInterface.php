<?php
namespace App\Backend\Service\IdentifiantGenerator;

interface IdentifiantGeneratorInterface
{
    /**
     * Génère un identifiant unique formaté pour une entité donnée.
     * Le format est PREFIXE-ANNEE-SEQUENCE (ex: RAP-2025-0015).
     * @param string $prefixe Le préfixe de l'identifiant (ex: 'RAP', 'ETU', 'PV').
     * @param int|null $annee Optionnel: l'année pour laquelle générer l'identifiant. Par défaut, l'année académique active.
     * @return string L'identifiant unique généré.
     * @throws \Exception En cas d'erreur de génération.
     */
    public function genererIdentifiantUnique(string $prefixe, ?int $annee = null): string;
}
