<?php

declare(strict_types=1);

namespace App\Backend\Service;

use PDO;
use App\Backend\Service\Interface\ReportingServiceInterface;
use App\Backend\Service\Interface\DocumentGeneratorServiceInterface;

class ServiceReportingAdmin implements ReportingServiceInterface
{
    private PDO $pdo;
    private DocumentGeneratorServiceInterface $documentGenerator;

    public function __construct(PDO $pdo, DocumentGeneratorServiceInterface $documentGenerator)
    {
        $this->pdo = $pdo;
        $this->documentGenerator = $documentGenerator;
    }

    public function genererRapportTauxValidation(array $filtres): array
    {
        $sql = "SELECT id_statut_rapport, COUNT(*) as count FROM rapport_etudiant GROUP BY id_statut_rapport";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    }

    public function genererRapportDelaisTraitement(array $filtres): array
    {
        $sql = "SELECT 
                    AVG(TIMESTAMPDIFF(HOUR, date_creation, date_soumission)) as delai_soumission,
                    AVG(TIMESTAMPDIFF(HOUR, date_soumission, date_verif_conformite)) as delai_conformite,
                    AVG(TIMESTAMPDIFF(HOUR, date_verif_conformite, date_validation_commission)) as delai_commission
                FROM rapport_etudiant
                WHERE date_validation_commission IS NOT NULL";

        $stmt = $this->pdo->query($sql);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function genererStatistiquesUtilisation(string $periode): array
    {
        $sql_users = "SELECT COUNT(*) as total_users FROM utilisateur";
        $sql_logins = "SELECT COUNT(*) as logins_this_month FROM pister WHERE code_action = 'USER_LOGIN_SUCCESS' AND date_action >= DATE_SUB(NOW(), INTERVAL 1 MONTH)";

        $users = $this->pdo->query($sql_users)->fetchColumn();
        $logins = $this->pdo->query($sql_logins)->fetchColumn();

        return [
            'utilisateurs_total' => $users,
            'connexions_dernier_mois' => $logins,
        ];
    }

    public function exporterDonnees(string $typeDonnees, string $format, array $filtres): string
    {
        $this->validerTypeDonnees($typeDonnees);
        $stmt = $this->pdo->query("SELECT * FROM {$typeDonnees}");
        $donnees = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($format === 'csv') {
            $cheminFichier = 'exports/' . $typeDonnees . '_' . time() . '.csv';
            $fp = fopen(__DIR__ . '/../../../Public/' . $cheminFichier, 'w');
            fputcsv($fp, array_keys($donnees[0]));
            foreach ($donnees as $ligne) {
                fputcsv($fp, $ligne);
            }
            fclose($fp);
            return $cheminFichier;
        }

        if ($format === 'pdf') {
            $html = "<h1>Export - {$typeDonnees}</h1><table><thead><tr>";
            foreach (array_keys($donnees[0]) as $header) {
                $html .= "<th>{$header}</th>";
            }
            $html .= "</tr></thead><tbody>";
            foreach ($donnees as $ligne) {
                $html .= "<tr>";
                foreach ($ligne as $cell) {
                    $html .= "<td>{$cell}</td>";
                }
                $html .= "</tr>";
            }
            $html .= "</tbody></table>";
            return $this->documentGenerator->genererPdfDepuisHtml($html, ['title' => "Export {$typeDonnees}"]);
        }

        throw new \InvalidArgumentException("Format d'export non supporté.");
    }

    public function creerDashboardPersonnalise(string $nom, array $widgets): string
    {
        // Une implémentation réelle nécessiterait des tables pour stocker les dashboards et widgets.
        // Ici, on retourne un placeholder.
        return "dashboard_{$nom}";
    }

    private function validerTypeDonnees(string $typeDonnees): void
    {
        $tablesAutorisees = ['etudiant', 'enseignant', 'rapport_etudiant', 'inscrire', 'faire_stage'];
        if (!in_array($typeDonnees, $tablesAutorisees)) {
            throw new \InvalidArgumentException("Type de données non autorisé pour l'export.");
        }
    }
}