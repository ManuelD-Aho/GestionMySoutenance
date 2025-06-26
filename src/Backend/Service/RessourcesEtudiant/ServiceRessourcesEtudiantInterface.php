<?php

namespace App\Backend\Service\RessourcesEtudiant;

interface ServiceRessourcesEtudiantInterface
{
    /**
     * Liste les guides méthodologiques disponibles pour la rédaction de rapports.
     * @return array
     */
    public function listerGuidesMethodologiques(): array;

    /**
     * Liste les exemples de structure de rapport.
     * @return array
     */
    public function listerExemplesRapport(): array;

    /**
     * Liste les critères d'évaluation appliqués par la commission.
     * @return array
     */
    public function listerCriteresEvaluation(): array;

    /**
     * Accède à la foire aux questions.
     * @return array
     */
    public function consulterFAQ(): array;

    /**
     * Récupère les coordonnées des services de support.
     * @return array
     */
    public function getCoordonneesSupport(): array;
}