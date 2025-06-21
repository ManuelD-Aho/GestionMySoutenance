<?php
namespace App\Backend\Service\Commission;

interface ServiceCommissionInterface
{
    /**
     * Crée une nouvelle session de validation de la commission.
     * @param string $libelleSession Le libellé de la session.
     * @param string $dateDebutSession La date et heure de début.
     * @param string $dateFinPrevue La date et heure de fin prévue.
     * @param string|null $numeroPresidentCommission Le numéro de l'enseignant président.
     * @param array $idsRapports Initialement rattachés à la session.
     * @return string L'ID de la session créée.
     * @throws \Exception En cas d'erreur.
     */
    public function creerSessionValidation(string $libelleSession, string $dateDebutSession, string $dateFinPrevue, ?string $numeroPresidentCommission = null, array $idsRapports = []): string;

    /**
     * Démarre une session de validation, passant son statut à 'En cours'.
     * @param string $idSession L'ID de la session.
     * @return bool Vrai si la mise à jour a réussi.
     * @throws \App\Backend\Exception\ElementNonTrouveException Si la session n'est pas trouvée.
     * @throws \App\Backend\Exception\OperationImpossibleException Si la session n'est pas 'Planifiee'.
     */
    public function demarrerSession(string $idSession): bool;

    /**
     * Clôture une session de validation, passant son statut à 'Cloturee'.
     * @param string $idSession L'ID de la session.
     * @return bool Vrai si la mise à jour a réussi.
     * @throws \App\Backend\Exception\ElementNonTrouveException Si la session n'est pas trouvée.
     * @throws \App\Backend\Exception\OperationImpossibleException Si la session n'est pas 'En cours' ou si des rapports ne sont pas finalisés.
     */
    public function cloturerSession(string $idSession): bool;

    /**
     * Récupère la liste des rapports assignés à un membre du jury pour une session donnée.
     * @param string $numeroEnseignant Le numéro de l'enseignant.
     * @param string|null $idSession L'ID de la session si spécifiée.
     * @return array Liste des rapports.
     */
    public function recupererRapportsAssignedToJury(string $numeroEnseignant, ?string $idSession = null): array;

    /**
     * Enregistre le vote d'un membre de la commission pour un rapport.
     * @param string $idRapportEtudiant L'ID du rapport.
     * @param string $numeroEnseignant Le numéro de l'enseignant votant.
     * @param string $idDecisionVote L'ID de la décision de vote.
     * @param string|null $commentaireVote Le commentaire associé au vote.
     * @param int $tourVote Le tour de vote actuel.
     * @param string|null $idSession L'ID de la session si le vote est rattaché à une session.
     * @return bool Vrai si le vote est enregistré.
     * @throws \App\Backend\Exception\ElementNonTrouveException Si rapport ou décision de vote n'existe pas.
     * @throws \App\Backend\Exception\OperationImpossibleException Si le commentaire est manquant ou si le membre a déjà voté.
     */
    public function enregistrerVotePourRapport(string $idRapportEtudiant, string $numeroEnseignant, string $idDecisionVote, ?string $commentaireVote, int $tourVote, ?string $idSession = null): bool;

    /**
     * Tente de finaliser la décision de la commission pour un rapport.
     * @param string $idRapportEtudiant L'ID du rapport.
     * @return bool Vrai si la décision a été finalisée.
     */
    public function finaliserDecisionCommissionPourRapport(string $idRapportEtudiant): bool;

    /**
     * Lance un nouveau tour de vote pour un rapport.
     * @param string $idRapportEtudiant L'ID du rapport.
     * @return bool Vrai si le nouveau tour est initié.
     * @throws \App\Backend\Exception\OperationImpossibleException Si le rapport n'est pas dans un état permettant un nouveau tour.
     */
    public function lancerNouveauTourVote(string $idRapportEtudiant): bool;

    /**
     * Rédige ou met à jour un Procès-Verbal (PV).
     * @param string $idRedacteur Le numéro de l'utilisateur rédacteur.
     * @param string $libellePv Le libellé ou titre du PV.
     * @param string $typePv Le type de PV ('Individuel' ou 'Session').
     * @param string|null $idRapportEtudiant L'ID du rapport si PV Individuel.
     * @param array $idsRapportsSession Les IDs des rapports si PV de Session.
     * @param string|null $idCompteRenduExistant L'ID du PV existant pour une mise à jour.
     * @return string L'ID du PV créé ou mis à jour.
     * @throws \Exception En cas d'erreur.
     */
    public function redigerOuMettreAJourPv(string $idRedacteur, string $libellePv, string $typePv, ?string $idRapportEtudiant = null, array $idsRapportsSession = [], ?string $idCompteRenduExistant = null): string;

    /**
     * Soumet un PV pour validation par les autres membres de la commission.
     * @param string $idCompteRendu L'ID du PV à soumettre.
     * @return bool Vrai si la soumission réussit.
     * @throws \App\Backend\Exception\ElementNonTrouveException Si le PV n'est pas trouvé.
     * @throws \App\Backend\Exception\OperationImpossibleException Si le statut du PV ne permet pas la soumission.
     */
    public function soumettrePvPourValidation(string $idCompteRendu): bool;

    /**
     * Gère la validation ou le rejet d'un PV par un membre de la commission.
     * @param string $idCompteRendu L'ID du PV.
     * @param string $numeroEnseignantValidateur Le numéro de l'enseignant qui valide/rejette.
     * @param string $idDecisionValidationPv L'ID de la décision.
     * @param string|null $commentaireValidation Le commentaire du validateur.
     * @return bool Vrai si la validation a réussi.
     * @throws \Exception En cas d'erreur.
     */
    public function validerOuRejeterPv(string $idCompteRendu, string $numeroEnseignantValidateur, string $idDecisionValidationPv, ?string $commentaireValidation): bool;
}