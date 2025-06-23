<?php
namespace App\Backend\Service\ModeleRapport;

use PDO;
use App\Backend\Model\RapportModele;
use App\Backend\Model\RapportModeleSection;
use App\Backend\Model\RapportModeleAssignation;
use App\Backend\Service\SupervisionAdmin\ServiceSupervisionAdminInterface;

class ServiceModeleRapport implements ServiceModeleRapportInterface
{
    public function __construct(PDO $db, ServiceSupervisionAdminInterface $supervisionService) {}
    public function creerModele(array $data): string { return "TPL-0001"; }
    public function modifierModele(string $idModele, array $data): bool { return true; }
    public function supprimerModele(string $idModele): bool { return true; }
    public function ajouterSectionAuModele(string $idModele, array $dataSection): bool { return true; }
    public function assignerModeleANiveau(string $idModele, string $idNiveauEtude): bool { return true; }
    public function listerModelesPourNiveau(string $idNiveauEtude): array { return []; }
    public function creerRapportDepuisModele(string $idModele, string $numeroEtudiant): string { return "RAP-2024-0001"; }
}