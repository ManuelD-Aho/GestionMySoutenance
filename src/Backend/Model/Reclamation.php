<?php
// src/Backend/Model/Reclamation.php

namespace App\Backend\Model;

use PDO;

class Reclamation extends BaseModel
{
    protected string $table = 'reclamation';
    protected string|array $primaryKey = 'id_reclamation';
    protected array $fields = [
        'id_reclamation', 'numero_carte_etudiant', 'sujet_reclamation', 'description_reclamation',
        'date_soumission', 'id_statut_reclamation', 'reponse_reclamation', 'date_reponse', 'numero_personnel_traitant'
    ];

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }

    public function getDetailsReclamation(string $idReclamation): ?array
    {
        $sql = "SELECT rec.*, 
                       et.nom as etudiant_nom, et.prenom as etudiant_prenom,
                       st.libelle_statut_reclamation,
                       pa.nom as personnel_nom, pa.prenom as personnel_prenom
                FROM `{$this->table}` rec
                JOIN `etudiant` et ON rec.numero_carte_etudiant = et.numero_carte_etudiant
                JOIN `statut_reclamation_ref` st ON rec.id_statut_reclamation = st.id_statut_reclamation
                LEFT JOIN `personnel_administratif` pa ON rec.numero_personnel_traitant = pa.numero_personnel_administratif
                WHERE rec.id_reclamation = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $idReclamation);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
}