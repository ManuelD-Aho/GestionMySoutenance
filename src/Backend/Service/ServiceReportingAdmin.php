<?php

namespace App\Backend\Service;

use App\Backend\Model\RapportEtudiant;
use App\Backend\Model\AnneeAcademique;
use App\Backend\Model\Utilisateur;
use App\Backend\Model\Enregistrer;
use PDO;

class ServiceReportingAdmin
{
    private RapportEtudiant $modeleRapportEtudiant;
    private AnneeAcademique $modeleAnneeAcademique;
    private Utilisateur $modeleUtilisateur;
    private Enregistrer $modeleEnregistrer;
    private PDO $db;

    public function __construct(
        RapportEtudiant $modeleRapportEtudiant,
        AnneeAcademique $modeleAnneeAcademique,
        Utilisateur $modeleUtilisateur,
        Enregistrer $modeleEnregistrer,
        PDO $db
    ) {
        $this->modeleRapportEtudiant = $modeleRapportEtudiant;
        $this->modeleAnneeAcademique = $modeleAnneeAcademique;
        $this->modeleUtilisateur = $modeleUtilisateur;
        $this->modeleEnregistrer = $modeleEnregistrer;
        $this->db = $db;
    }

    public function genererRapportTauxValidation(int $idAnneeAcademique, ?string $critereSupplementaire = null): array
    {
        $sql = "SELECT sr.libelle as statut_rapport, COUNT(re.id_rapport_etudiant) as nombre_rapports
                FROM rapport_etudiant re
                JOIN etudiant et ON re.numero_carte_etudiant = et.numero_carte_etudiant
                JOIN inscrire i ON et.numero_carte_etudiant = i.numero_carte_etudiant AND i.id_annee_academique = :id_annee_academique
                JOIN statut_rapport_ref sr ON re.id_statut_rapport = sr.id_statut_rapport ";

        $parametres = [':id_annee_academique' => $idAnneeAcademique];
        $conditionsSupplementaires = "";

        if ($critereSupplementaire) {
            // Exemple: $conditionsSupplementaires = " AND et.id_niveau_etude = :id_niveau";
            // $parametres[':id_niveau'] = $valeurCritere;
        }
        $sql .= $conditionsSupplementaires . " GROUP BY sr.id_statut_rapport, sr.libelle ORDER BY sr.id_statut_rapport";

        $declaration = $this->db->prepare($sql);
        $declaration->execute($parametres);
        return $declaration->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function genererRapportDelaisMoyensParEtape(): array
    {
        // Cette méthode nécessite une modélisation plus poussée du suivi des dates de changement de statut.
        // Par exemple, une table historique des statuts de rapport.
        // Pour l'instant, elle retourne un placeholder.
        return ["message_informatif" => "La génération du rapport des délais moyens nécessite une table d'historique des statuts."];
    }

    public function genererStatistiquesUtilisation(): array
    {
        $nbUtilisateursActifs = $this->modeleUtilisateur->compterParCritere(['actif' => 1]);
        $actionsRecentes = $this->modeleEnregistrer->executerRequete(
            "SELECT a.lib_action, COUNT(e.id_action) as nombre_occurrences
             FROM enregistrer e
             JOIN action a ON e.id_action = a.id_action
             WHERE e.date_action >= DATE_SUB(NOW(), INTERVAL 7 DAY)
             GROUP BY e.id_action, a.lib_action
             ORDER BY nombre_occurrences DESC
             LIMIT 10"
        )->fetchAll(PDO::FETCH_ASSOC);

        return [
            'nombre_utilisateurs_actifs' => $nbUtilisateursActifs,
            'actions_frequentes_semaine_passee' => $actionsRecentes ?: []
        ];
    }
}