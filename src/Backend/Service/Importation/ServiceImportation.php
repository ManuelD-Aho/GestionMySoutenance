<?php
namespace App\Backend\Service\Importation;

use PDO;
use App\Backend\Service\SupervisionAdmin\ServiceSupervisionAdminInterface;

class ServiceImportation implements ServiceImportationInterface
{
    public function __construct(PDO $db, ServiceSupervisionAdminInterface $supervisionService) {}
    public function analyserFichier(string $cheminFichier): array { return []; }
    public function previsualiserDonnees(string $cheminFichier, array $mappage): array { return []; }
    public function lancerImportationAsynchrone(string $cheminFichier, array $mappage, string $typeEntite): string { return "task_id_123"; }
    public function getStatutImportation(string $idTache): array { return []; }
}