<?php

namespace App\Backend\Service\Conformite;

interface ServiceConformiteInterface
{
    /**
     * Traite la vérification de conformité d'un rapport étudiant par un membre du personnel administratif.
     * @param string $idRapportEtudiant L'ID du rapport étudiant.
     * @param string $numeroPersonnelAdministratif Le numéro du personnel effectuant la vérification.
     * @param string $idStatutConformite L'ID du statut de conformité ('CONF_OK' ou 'CONF_NOK').
     * @param string|null $commentaireConformite Le commentaire du vérificateur.
     * @return bool Vrai si le traitement a réussi.
     * @throws \Exception En cas d'erreur.
     */
    public function traiterVerificationConformite(string $idRapportEtudiant, string $numeroPersonnelAdministratif, string $idStatutConformite, ?string $commentaireConformite): bool;

    /**
     * Récupère la liste des rapports en attente de vérification de conformité.
     * @return array Liste des rapports.
     */
    public function recupererRapportsEnAttenteDeVerification(): array;

    /**
     * Récupère la liste des rapports traités par un agent de conformité spécifique.
     * @param string $numeroPersonnelAdministratif Le numéro du personnel.
     * @return array Liste des rapports traités.
     */
    public function recupererRapportsTraitesParAgent(string $numeroPersonnelAdministratif): array;

    /**
     * Récupère une vérification de conformité spécifique par l'agent et le rapport.
     * @param string $numeroPersonnelAdministratif Le numéro de l'agent.
     * @param string $idRapportEtudiant L'ID du rapport.
     * @return array|null Les détails de la vérification ou null si non trouvée.
     */
    public function getVerificationByAgentAndRapport(string $numeroPersonnelAdministratif, string $idRapportEtudiant): ?array;
}