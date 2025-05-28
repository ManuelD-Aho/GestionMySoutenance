<?php

namespace App\Backend\Service\Rapport;

use App\Backend\Model\DocumentSoumis;
use App\Backend\Model\RapportEtudiant;
use PDO;

class ServiceRapport
{
    private RapportEtudiant $modeleRapportEtudiant;
    private DocumentSoumis $modeleDocumentSoumis;
    private PDO $db;

    public function __construct(
        RapportEtudiant $modeleRapportEtudiant,
        DocumentSoumis $modeleDocumentSoumis,
        PDO $db
    ) {
        $this->modeleRapportEtudiant = $modeleRapportEtudiant;
        $this->modeleDocumentSoumis = $modeleDocumentSoumis;
        $this->db = $db;
    }

    public function creerNouveauRapport(string $numeroCarteEtudiant, string $libelleRapport, ?string $theme, ?string $resume, ?string $numeroAttestationStage, ?int $nombrePages): ?int
    {
        $donneesRapport = [
            'numero_carte_etudiant' => $numeroCarteEtudiant,
            'libelle_rapport_etudiant' => $libelleRapport,
            'theme' => $theme,
            'resume' => $resume,
            'numero_attestation_stage' => $numeroAttestationStage,
            'nombre_pages' => $nombrePages,
            'id_statut_rapport' => 1, // 1: Brouillon
            'date_soumission' => null,
            'date_derniere_modif' => date('Y-m-d H:i:s')
        ];
        $idRapport = $this->modeleRapportEtudiant->creer($donneesRapport);
        return $idRapport ? (int)$idRapport : null;
    }

    public function soumettreRapportPourVerification(int $idRapportEtudiant): bool
    {
        return $this->modeleRapportEtudiant->mettreAJourParIdentifiant($idRapportEtudiant, [
            'id_statut_rapport' => 2, // 2: Soumis
            'date_soumission' => date('Y-m-d H:i:s'),
            'date_derniere_modif' => date('Y-m-d H:i:s')
        ]);
    }

    public function ajouterDocumentARapport(int $idRapportEtudiant, string $cheminFichier, string $nomOriginal, string $typeMime, int $tailleFichier, int $idTypeDocument, string $numeroUtilisateurUpload, int $version = 1): bool
    {
        $donneesDocument = [
            'id_rapport_etudiant' => $idRapportEtudiant,
            'chemin_fichier' => $cheminFichier,
            'nom_original' => $nomOriginal,
            'type_mime' => $typeMime,
            'taille_fichier' => $tailleFichier,
            'id_type_document' => $idTypeDocument,
            'numero_utilisateur_upload' => $numeroUtilisateurUpload,
            'version' => $version,
            'date_upload' => date('Y-m-d H:i:s')
        ];
        return (bool)$this->modeleDocumentSoumis->creer($donneesDocument);
    }

    public function recupererInformationsRapportComplet(int $idRapportEtudiant): ?array
    {
        $rapport = $this->modeleRapportEtudiant->trouverParIdentifiant($idRapportEtudiant);
        if ($rapport) {
            $rapport['documents'] = $this->modeleDocumentSoumis->trouverParCritere(['id_rapport_etudiant' => $idRapportEtudiant]);
        }
        return $rapport;
    }

    public function mettreAJourStatutRapport(int $idRapportEtudiant, int $idNouveauStatut, ?string $commentaireOptionnel = null): bool
    {
        return $this->modeleRapportEtudiant->mettreAJourParIdentifiant($idRapportEtudiant, [
            'id_statut_rapport' => $idNouveauStatut,
            'date_derniere_modif' => date('Y-m-d H:i:s')
        ]);
    }

    public function enregistrerCorrectionsSoumises(int $idRapportEtudiant, array $fichiersDocumentsCorriges, string $numeroUtilisateurUpload, ?string $noteExplicative): bool
    {
        $this->db->beginTransaction();
        try {
            foreach ($fichiersDocumentsCorriges as $doc) {
                $versionPrecedente = $this->modeleDocumentSoumis->trouverUnParCritere(
                    ['id_rapport_etudiant' => $idRapportEtudiant, 'id_type_document' => $doc['id_type_document']],
                    ['MAX(version) as max_version']
                );
                $nouvelleVersion = $versionPrecedente && $versionPrecedente['max_version'] ? $versionPrecedente['max_version'] + 1 : 1;

                if (!$this->ajouterDocumentARapport($idRapportEtudiant, $doc['chemin_fichier'], $doc['nom_original'], $doc['type_mime'], $doc['taille_fichier'], $doc['id_type_document'], $numeroUtilisateurUpload, $nouvelleVersion)) {
                    $this->db->rollBack();
                    return false;
                }
            }

            if (!$this->mettreAJourStatutRapport($idRapportEtudiant, 5)) { // 5: En Commission (ou un statut "Corrections Soumises")
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
}