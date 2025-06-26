<?php

namespace App\Backend\Service\IdentifiantGenerator;

use App\Backend\Exception\ElementNonTrouveException;
use App\Backend\Exception\OperationImpossibleException;

interface IdentifiantGeneratorInterface
{
    /**
     * Génère un identifiant unique formaté pour une entité donnée.
     * Le format est PREFIXE-ANNEE-SEQUENCE (ex: RAP-2025-0015).
     * Cette méthode est atomique et sécurisée contre les accès concurrents.
     *
     * @param string $prefixe Le préfixe de l'identifiant (ex: 'RAP', 'ETU', 'PV_').
     * @param int|null $annee Optionnel: l'année pour laquelle générer l'identifiant. Par défaut, l'année académique active.
     * @return string L'identifiant unique généré.
     * @throws ElementNonTrouveException Si aucune année académique active n'est trouvée.
     * @throws OperationImpossibleException En cas d'erreur de génération.
     */
    public function genererIdentifiantUnique(string $prefixe, ?int $annee = null): string;
}