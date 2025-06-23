<?php
namespace App\Backend\Service\Delegation;

interface ServiceDelegationInterface
{
    public function creerDelegation(string $idDelegant, string $idDelegue, array $idsTraitements, \DateTime $dateDebut, \DateTime $dateFin, ?string $contexteType = null, ?string $contexteId = null): bool;
    public function revoquerDelegation(string $idDelegation): bool;
    public function listerDelegationsActivesPourUtilisateur(string $idUtilisateur): array;
    public function getPermissionsDeleguees(string $idUtilisateur): array;
}