<?php

namespace App\Backend\Service\WorkflowSoutenance;

interface ServiceWorkflowSoutenanceInterface
{
    /**
     * Démarre le processus de soutenance pour un étudiant.
     * @param string $numeroEtudiant Le numéro de l'étudiant.
     * @param string $idAnneeAcademique L'ID de l'année académique.
     * @return string L'ID du workflow créé.
     */
    public function demarrerWorkflow(string $numeroEtudiant, string $idAnneeAcademique): string;

    /**
     * Soumet un rapport de soutenance.
     * @param string $idWorkflow L'ID du workflow.
     * @param array $donneesRapport Les données du rapport.
     * @return bool Vrai si la soumission a réussi.
     */
    public function soumettre Rapport(string $idWorkflow, array $donneesRapport): bool;

    /**
     * Valide la conformité administrative d'un rapport.
     * @param string $idRapport L'ID du rapport.
     * @param string $numeroPersonnelAdmin Le numéro du personnel administratif.
     * @param array $resultatsConformite Les résultats de la vérification.
     * @return bool Vrai si la validation a réussi.
     */
    public function validerConformiteAdministrative(string $idRapport, string $numeroPersonnelAdmin, array $resultatsConformite): bool;

    /**
     * Affecte un jury à un rapport.
     * @param string $idRapport L'ID du rapport.
     * @param array $membresJury Les membres du jury.
     * @return bool Vrai si l'affectation a réussi.
     */
    public function affecterJury(string $idRapport, array $membresJury): bool;

    /**
     * Programme une session de validation commission.
     * @param array $donneesSession Les données de la session.
     * @param array $idsRapports Les IDs des rapports à inclure.
     * @return string L'ID de la session créée.
     */
    public function programmerSessionValidation(array $donneesSession, array $idsRapports): string;

    /**
     * Enregistre un vote pour un rapport.
     * @param string $idRapport L'ID du rapport.
     * @param string $numeroEnseignant Le numéro de l'enseignant.
     * @param array $donneesVote Les données du vote.
     * @return bool Vrai si le vote a été enregistré.
     */
    public function enregistrerVote(string $idRapport, string $numeroEnseignant, array $donneesVote): bool;

    /**
     * Finalise la décision de commission pour un rapport.
     * @param string $idRapport L'ID du rapport.
     * @return bool Vrai si la finalisation a réussi.
     */
    public function finaliserDecisionCommission(string $idRapport): bool;

    /**
     * Génère les documents officiels (PV, attestations).
     * @param string $idRapport L'ID du rapport.
     * @param array $typesDocuments Les types de documents à générer.
     * @return array Les chemins des documents générés.
     */
    public function genererDocumentsOfficiels(string $idRapport, array $typesDocuments): array;

    /**
     * Récupère l'état actuel d'un workflow.
     * @param string $idWorkflow L'ID du workflow.
     * @return array L'état détaillé du workflow.
     */
    public function obtenirEtatWorkflow(string $idWorkflow): array;

    /**
     * Liste les workflows selon des critères.
     * @param array $criteres Les critères de filtrage.
     * @param int $page Le numéro de page.
     * @param int $elementsParPage Le nombre d'éléments par page.
     * @return array La liste paginée des workflows.
     */
    public function listerWorkflows(array $criteres = [], int $page = 1, int $elementsParPage = 20): array;

    /**
     * Avance un workflow à l'étape suivante.
     * @param string $idWorkflow L'ID du workflow.
     * @param string $etapeSuivante L'étape suivante.
     * @param array $donneesTransition Les données de transition.
     * @return bool Vrai si la transition a réussi.
     */
    public function avancerEtape(string $idWorkflow, string $etapeSuivante, array $donneesTransition): bool;

    /**
     * Annule ou suspend un workflow.
     * @param string $idWorkflow L'ID du workflow.
     * @param string $action L'action (SUSPENDRE, ANNULER).
     * @param string $raison La raison de l'action.
     * @return bool Vrai si l'action a réussi.
     */
    public function gererSuspensionAnnulation(string $idWorkflow, string $action, string $raison): bool;
}