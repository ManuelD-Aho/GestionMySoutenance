<?php
// src/Backend/Model/RapportEtudiant.php

namespace App\Backend\Model;

use PDO;

class RapportEtudiant extends BaseModel
{
    public string $table = 'rapport_etudiant';
    protected string|array $primaryKey = 'id_rapport_etudiant';
    protected array $fields = [
        'id_rapport_etudiant', 'libelle_rapport_etudiant', 'theme', 'resume', 'numero_attestation_stage',
        'numero_carte_etudiant', 'nombre_pages', 'id_statut_rapport', 'date_soumission', 'date_derniere_modif'
    ];

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }

    public function trouverRapportsAvecDetailsEtudiant(array $criteres = []): array
    {
        $sql = "SELECT r.*, e.nom, e.prenom, s.libelle_statut_rapport
                FROM `{$this->table}` r 
                JOIN `etudiant` e ON r.numero_carte_etudiant = e.numero_carte_etudiant
                JOIN `statut_rapport_ref` s ON r.id_statut_rapport = s.id_statut_rapport";

        $params = [];
        if (!empty($criteres)) {
            $whereParts = [];
            foreach ($criteres as $key => $value) {
                $whereParts[] = "r.`{$key}` = :{$key}";
                $params[":{$key}"] = $value;
            }
            $sql .= " WHERE " . implode(" AND ", $whereParts);
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}