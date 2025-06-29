<?php

declare(strict_types=1);

namespace App\Backend\Service\Interface;

use App\Backend\Exception\OperationImpossibleException;

interface IdentifiantGeneratorInterface
{
    /**
     * Génère un identifiant métier unique, annuel et séquentiel.
     *
     * @param string $prefixe Le préfixe de 3 lettres identifiant l'entité (ex: 'RAP', 'ETU').
     * @return string L'identifiant généré (ex: 'RAP-2025-0001').
     * @throws OperationImpossibleException Si la séquence pour l'année est saturée.
     */
    public function generer(string $prefixe): string;
}