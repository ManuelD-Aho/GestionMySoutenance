<?php
namespace App\Backend\Service\Transition;

interface ServiceTransitionInterface
{
    public function trouverTachesOrphelines(string $numeroUtilisateur): array;
    public function reassignerVote(string $idVote, string $nouveauNumeroEnseignant): bool;
    public function reassignerValidationPv(string $idValidation, string $nouveauNumeroEnseignant): bool;
    public function reassignerRapportConformite(string $idRapport, string $nouveauNumeroPersonnel): bool;
}