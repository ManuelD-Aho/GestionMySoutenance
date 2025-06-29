<?php

declare(strict_types=1);

namespace App\Backend\Service\Interface;

interface ProcesVerbalServiceInterface
{
    /**
     * Crée un nouveau compte-rendu associé à un rapport.
     *
     * @param string $idRapport ID du rapport concerné.
     * @param string $idRedacteur ID de l'utilisateur rédacteur.
     * @param string $libelle Le titre ou libellé du compte-rendu.
     * @param \DateTimeInterface $dateLimiteApprobation La date limite pour les approbations.
     * @return string L'ID du compte-rendu créé.
     */
    public function creerCompteRendu(string $idRapport, string $idRedacteur, string $libelle, \DateTimeInterface $dateLimiteApprobation): string;

    /**
     * Enregistre la décision (approbation/rejet) d'un membre de la commission sur un compte-rendu.
     * Cette opération est idempotente.
     *
     * @param string $idCompteRendu ID du compte-rendu.
     * @param string $numeroEnseignant ID du membre qui vote.
     * @param string $idDecision ID de la décision (ex: 'DEC_APPROUVE').
     * @param string|null $commentaire Commentaire optionnel.
     * @return bool
     */
    public function enregistrerApprobation(string $idCompteRendu, string $numeroEnseignant, string $idDecision, ?string $commentaire): bool;

    /**
     * Tente de finaliser un compte-rendu.
     * La finalisation réussit uniquement si tous les membres de la commission ont approuvé le document.
     *
     * @param string $idCompteRendu ID du compte-rendu à finaliser.
     * @return bool True si la finalisation a réussi.
     * @throws \App\Backend\Exception\OperationImpossibleException si les conditions de finalisation ne sont pas remplies.
     */
    public function finaliserCompteRendu(string $idCompteRendu): bool;
}