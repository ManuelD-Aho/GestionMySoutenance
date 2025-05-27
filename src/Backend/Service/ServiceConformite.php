<?php

namespace App\Backend\Service;

use App\Backend\Model\Approuver;
use App\Backend\Model\RapportEtudiant;
use App\Backend\Model\DocumentSoumis;
use App\Backend\Model\StatutRapportRef;
use App\Backend\Model\StatutConformiteRef;
use PDO;

class ServiceConformite
{
    private Approuver $modeleApprouver;
    private RapportEtudiant $modeleRapportEtudiant;
    private PDO $db;

    public function __construct(
        Approuver $modeleApprouver,
        RapportEtudiant $modeleRapportEtudiant,
        PDO $db
    ) {
        $this->modeleApprouver = $modeleApprouver;
        $this->modeleRapportEtudiant = $modeleRapportEtudiant;
        $this->db = $db;
    }

    public function traiterVerificationConformite(int $idRapportEtudiant, string $numeroPersonnelAdministratif, int $idStatutConformite, ?string $commentaireConformite): bool
    {
        $this->db->beginTransaction();
        try {
            $donneesApprobation = [
                'numero_personnel_administratif' => $numeroPersonnelAdministratif,
                'id_rapport_etudiant' => $idRapportEtudiant,
                'id_statut_conformite' => $idStatutConformite,
                'commentaire_conformite' => $commentaireConformite,
                'date_verification_conformite' => date('Y-m-d H:i:s')
            ];

            if (!$this->modeleApprouver->creer($donneesApprobation)) {
                $this->db->rollBack();
                return false;
            }

            $idNouveauStatutRapport = ($idStatutConformite == 1) ? 4 : 3; // 4: Conforme, 3: Non Conforme (selon ID dans statut_rapport_ref)

            if (!$this->modeleRapportEtudiant->mettreAJourParIdentifiant($idRapportEtudiant, ['id_statut_rapport' => $idNouveauStatutRapport])) {
                $this->db->rollBack();
                return false;
            }

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function recupererRapportsEnAttenteDeVerification(): array
    {
        return $this->modeleRapportEtudiant->trouverParCritere(['id_statut_rapport' => 2]); // 2: Soumis
    }

    public function recupererRapportsTraitesParAgent(string $numeroPersonnelAdministratif): array
    {
        $sql = "SELECT r.* FROM rapport_etudiant r JOIN approuver a ON r.id_rapport_etudiant = a.id_rapport_etudiant WHERE a.numero_personnel_administratif = :num_personnel";
        $declaration = $this->modeleRapportEtudiant->executerRequete($sql, [':num_personnel' => $numeroPersonnelAdministratif]);
        return $declaration->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}