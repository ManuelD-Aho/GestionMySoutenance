<?php

namespace App\Backend\Service\Rapport;

use PDO;
use App\Backend\Model\RapportEtudiant;
use App\Backend\Model\StatutRapportRef;
use App\Backend\Model\Utilisateur;
use App\Backend\Model\SectionRapport;
use App\Backend\Model\DocumentGenere;
use App\Backend\Model\Approuver;
use App\Backend\Model\VoteCommission;
use App\Backend\Model\CompteRendu;
use App\Backend\Service\Notification\ServiceNotificationInterface;
use App\Backend\Service\SupervisionAdmin\ServiceSupervisionAdminInterface;
use App\Backend\Service\IdentifiantGenerator\IdentifiantGeneratorInterface;
use App\Backend\Exception\ElementNonTrouveException;
use App\Backend\Exception\OperationImpossibleException;

class ServiceRapport implements ServiceRapportInterface
{
    private RapportEtudiant $rapportEtudiantModel;
    private StatutRapportRef $statutRapportRefModel;
    private Utilisateur $utilisateurModel;
    private SectionRapport $sectionRapportModel;
    private DocumentGenere $documentGenereModel;
    private Approuver $approuverModel;
    private VoteCommission $voteCommissionModel;
    private CompteRendu $compteRenduModel;
    private ServiceNotificationInterface $notificationService;
    private ServiceSupervisionAdminInterface $supervisionService;
    private IdentifiantGeneratorInterface $idGenerator;

    public function __construct(
        PDO $db,
        RapportEtudiant $rapportEtudiantModel,
        StatutRapportRef $statutRapportRefModel,
        Utilisateur $utilisateurModel,
        SectionRapport $sectionRapportModel,
        DocumentGenere $documentGenereModel,
        Approuver $approuverModel,
        VoteCommission $voteCommissionModel,
        CompteRendu $compteRenduModel,
        ServiceNotificationInterface $notificationService,
        ServiceSupervisionAdminInterface $supervisionService,
        IdentifiantGeneratorInterface $idGenerator
    ) {
        $this->rapportEtudiantModel = $rapportEtudiantModel;
        $this->statutRapportRefModel = $statutRapportRefModel;
        $this->utilisateurModel = $utilisateurModel;
        $this->sectionRapportModel = $sectionRapportModel;
        $this->documentGenereModel = $documentGenereModel;
        $this->approuverModel = $approuverModel;
        $this->voteCommissionModel = $voteCommissionModel;
        $this->compteRenduModel = $compteRenduModel;
        $this->notificationService = $notificationService;
        $this->supervisionService = $supervisionService;
        $this->idGenerator = $idGenerator;
    }

    public function creerOuMettreAJourBrouillonRapport(string $numeroCarteEtudiant, array $metadonnees, array $sectionsContenu): string
    {
        if (!$this->utilisateurModel->trouverParIdentifiant($numeroCarteEtudiant)) {
            throw new ElementNonTrouveException("Étudiant non trouvé.");
        }

        $this->rapportEtudiantModel->commencerTransaction();
        try {
            $existingRapport = $this->rapportEtudiantModel->trouverUnParCritere([
                'numero_carte_etudiant' => $numeroCarteEtudiant,
                'id_statut_rapport' => 'RAP_BROUILLON'
            ]);

            $idRapport = null;
            $actionType = 'CREATION_BROUILLON_RAPPORT';
            $actionDetails = "Nouveau brouillon de rapport créé pour {$numeroCarteEtudiant}.";

            if ($existingRapport) {
                $idRapport = $existingRapport['id_rapport_etudiant'];
                $metadonnees['date_derniere_modif'] = date('Y-m-d H:i:s');
                if (!$this->rapportEtudiantModel->mettreAJourParIdentifiant($idRapport, $metadonnees)) {
                    throw new OperationImpossibleException("Échec de la mise à jour du rapport brouillon existant.");
                }
                $actionType = 'MAJ_BROUILLON_RAPPORT';
                $actionDetails = "Brouillon de rapport {$idRapport} de {$numeroCarteEtudiant} mis à jour.";
            } else {
                $idRapport = $this->idGenerator->genererIdentifiantUnique('RAP');
                $metadonnees['id_rapport_etudiant'] = $idRapport;
                $metadonnees['numero_carte_etudiant'] = $numeroCarteEtudiant;
                $metadonnees['id_statut_rapport'] = 'RAP_BROUILLON';
                $metadonnees['date_soumission'] = null;
                $metadonnees['date_derniere_modif'] = date('Y-m-d H:i:s');

                if (!$this->rapportEtudiantModel->creer($metadonnees)) {
                    throw new OperationImpossibleException("Échec de la création du nouveau rapport brouillon.");
                }
            }

            foreach ($sectionsContenu as $titreSection => $contenu) {
                $existingSection = $this->sectionRapportModel->trouverSectionUnique($idRapport, $titreSection);
                $sectionData = ['contenu_section' => $contenu];
                if ($existingSection) {
                    $this->sectionRapportModel->mettreAJourParClesInternes(['id_rapport_etudiant' => $idRapport, 'titre_section' => $titreSection], $sectionData);
                } else {
                    $sectionData['id_rapport_etudiant'] = $idRapport;
                    $sectionData['titre_section'] = $titreSection;
                    $this->sectionRapportModel->creer($sectionData);
                }
            }

            $this->rapportEtudiantModel->validerTransaction();
            $this->supervisionService->enregistrerAction(
                $numeroCarteEtudiant,
                $actionType,
                $actionDetails,
                $idRapport,
                'RapportEtudiant'
            );
            return $idRapport;
        } catch (\Exception $e) {
            $this->rapportEtudiantModel->annulerTransaction();
            throw $e;
        }
    }

    public function soumettreRapportPourVerification(string $idRapportEtudiant): bool
    {
        $rapport = $this->rapportEtudiantModel->trouverParIdentifiant($idRapportEtudiant);
        if (!$rapport) {
            throw new ElementNonTrouveException("Rapport étudiant '{$idRapportEtudiant}' non trouvé.");
        }

        if (!in_array($rapport['id_statut_rapport'], ['RAP_BROUILLON', 'RAP_NON_CONF', 'RAP_CORRECT'])) {
            throw new OperationImpossibleException("Le rapport '{$rapport['id_rapport_etudiant']}' ne peut être soumis que s'il est en brouillon ou s'il a été retourné pour corrections.");
        }

        $this->rapportEtudiantModel->commencerTransaction();
        try {
            $success = $this->rapportEtudiantModel->mettreAJourParIdentifiant($idRapportEtudiant, [
                'id_statut_rapport' => 'RAP_SOUMIS',
                'date_soumission' => date('Y-m-d H:i:s'),
                'date_derniere_modif' => date('Y-m-d H:i:s')
            ]);

            if (!$success) {
                throw new OperationImpossibleException("Échec de la mise à jour du statut du rapport en 'Soumis'.");
            }

            $this->notificationService->envoyerNotificationUtilisateur(
                $rapport['numero_carte_etudiant'],
                'RAPPORT_SOUMIS',
                "Votre rapport '{$rapport['libelle_rapport_etudiant']}' a été soumis avec succès pour vérification."
            );

            $this->notificationService->envoyerNotificationGroupe(
                'GRP_AGENT_CONFORMITE',
                'NOUVEAU_RAPPORT_SOUMIS',
                "Un nouveau rapport ('{$rapport['libelle_rapport_etudiant']}') a été soumis et attend la vérification de conformité."
            );

            $this->rapportEtudiantModel->validerTransaction();
            $this->supervisionService->enregistrerAction(
                $rapport['numero_carte_etudiant'],
                'SOUMISSION_RAPPORT',
                "Rapport '{$idRapportEtudiant}' soumis pour vérification.",
                $idRapportEtudiant,
                'RapportEtudiant'
            );
            return true;
        } catch (\Exception $e) {
            $this->rapportEtudiantModel->annulerTransaction();
            throw $e;
        }
    }

    public function enregistrerCorrectionsSoumises(string $idRapportEtudiant, array $sectionsContenuCorriges, string $numeroUtilisateurUpload, ?string $noteExplicative = null): bool
    {
        $rapport = $this->rapportEtudiantModel->trouverParIdentifiant($idRapportEtudiant);
        if (!$rapport) {
            throw new ElementNonTrouveException("Rapport étudiant '{$idRapportEtudiant}' non trouvé.");
        }
        if ($rapport['numero_carte_etudiant'] !== $numeroUtilisateurUpload) {
            throw new OperationImpossibleException("L'utilisateur n'est pas autorisé à soumettre des corrections pour ce rapport.");
        }

        if (!in_array($rapport['id_statut_rapport'], ['RAP_NON_CONF', 'RAP_CORRECT'])) {
            throw new OperationImpossibleException("Le rapport '{$idRapportEtudiant}' n'est pas dans un état ('Non Conforme' ou 'Corrections Demandées') permettant la soumission de corrections.");
        }

        $this->rapportEtudiantModel->commencerTransaction();
        try {
            foreach ($sectionsContenuCorriges as $titreSection => $contenu) {
                $existingSection = $this->sectionRapportModel->trouverSectionUnique($idRapportEtudiant, $titreSection);
                $sectionData = ['contenu_section' => $contenu];
                if ($existingSection) {
                    $this->sectionRapportModel->mettreAJourParClesInternes(['id_rapport_etudiant' => $idRapportEtudiant, 'titre_section' => $titreSection], $sectionData);
                } else {
                    $sectionData['id_rapport_etudiant'] = $idRapportEtudiant;
                    $sectionData['titre_section'] = $titreSection;
                    $this->sectionRapportModel->creer($sectionData);
                }
            }

            $success = $this->rapportEtudiantModel->mettreAJourParIdentifiant($idRapportEtudiant, [
                'id_statut_rapport' => 'RAP_SOUMIS',
                'date_derniere_modif' => date('Y-m-d H:i:s')
            ]);

            if (!$success) {
                throw new OperationImpossibleException("Échec de la mise à jour du statut du rapport après corrections.");
            }

            $this->rapportEtudiantModel->validerTransaction();
            $this->supervisionService->enregistrerAction(
                $numeroUtilisateurUpload,
                'SOUMISSION_CORRECTIONS',
                "Corrections soumises pour le rapport '{$idRapportEtudiant}' par {$numeroUtilisateurUpload}",
                $idRapportEtudiant,
                'RapportEtudiant',
                ['note_explicative' => $noteExplicative]
            );
            $this->notificationService->envoyerNotificationGroupe(
                'GRP_AGENT_CONFORMITE',
                'CORRECTIONS_RAPPORT_SOUMISES',
                "Des corrections ont été soumises pour le rapport '{$rapport['libelle_rapport_etudiant']}' (ID: {$idRapportEtudiant})."
            );
            return true;
        } catch (\Exception $e) {
            $this->rapportEtudiantModel->annulerTransaction();
            throw $e;
        }
    }

    public function recupererInformationsRapportComplet(string $idRapportEtudiant): ?array
    {
        $rapport = $this->rapportEtudiantModel->trouverParIdentifiant($idRapportEtudiant);
        if (!$rapport) {
            return null;
        }

        $rapport['sections'] = $this->sectionRapportModel->trouverSectionsPourRapport($idRapportEtudiant);
        $rapport['conformite_historique'] = $this->approuverModel->trouverParCritere(['id_rapport_etudiant' => $idRapportEtudiant], ['*'], 'AND', 'date_verification_conformite DESC');
        $rapport['votes'] = $this->voteCommissionModel->trouverParCritere(['id_rapport_etudiant' => $idRapportEtudiant], ['*'], 'AND', 'date_vote DESC');
        $rapport['pv_final'] = $this->compteRenduModel->trouverUnParCritere(['id_rapport_etudiant' => $idRapportEtudiant, 'id_statut_pv' => 'PV_VALID']);
        $rapport['fichiers_joints'] = $this->documentGenereModel->trouverParEntiteSource($idRapportEtudiant, 'RAPPORT');

        return $rapport;
    }

    public function mettreAJourStatutRapport(string $idRapportEtudiant, string $newStatutId): bool
    {
        if (!$this->rapportEtudiantModel->trouverParIdentifiant($idRapportEtudiant)) {
            throw new ElementNonTrouveException("Rapport étudiant non trouvé.");
        }
        if (!$this->statutRapportRefModel->trouverParIdentifiant($newStatutId)) {
            throw new ElementNonTrouveException("Statut de rapport '{$newStatutId}' non reconnu.");
        }

        $success = $this->rapportEtudiantModel->mettreAJourParIdentifiant($idRapportEtudiant, ['id_statut_rapport' => $newStatutId]);

        if ($success) {
            $this->supervisionService->enregistrerAction(
                $_SESSION['user_id'] ?? 'SYSTEM',
                'MAJ_STATUT_RAPPORT',
                "Statut du rapport '{$idRapportEtudiant}' changé à '{$newStatutId}'",
                $idRapportEtudiant,
                'RapportEtudiant'
            );
        }
        return $success;
    }

    public function reactiverEditionRapport(string $idRapportEtudiant, string $motifActivation = 'Reprise demandée'): bool
    {
        $rapport = $this->rapportEtudiantModel->trouverParIdentifiant($idRapportEtudiant);
        if (!$rapport) {
            throw new ElementNonTrouveException("Rapport étudiant non trouvé.");
        }

        if (!in_array($rapport['id_statut_rapport'], ['RAP_REFUSE', 'RAP_CORRECT'])) {
            throw new OperationImpossibleException("Le rapport '{$idRapportEtudiant}' n'est pas dans un état permettant la réactivation de l'édition.");
        }

        $this->rapportEtudiantModel->commencerTransaction();
        try {
            $success = $this->rapportEtudiantModel->mettreAJourParIdentifiant($idRapportEtudiant, [
                'id_statut_rapport' => 'RAP_BROUILLON',
                'date_derniere_modif' => date('Y-m-d H:i:s')
            ]);

            if (!$success) {
                throw new OperationImpossibleException("Échec de la réactivation de l'édition du rapport.");
            }

            $this->rapportEtudiantModel->validerTransaction();
            $this->supervisionService->enregistrerAction(
                $_SESSION['user_id'] ?? $rapport['numero_carte_etudiant'],
                'REACTIVATION_RAPPORT_EDITION',
                "Édition du rapport '{$idRapportEtudiant}' réactivée. Motif: '{$motifActivation}'",
                $idRapportEtudiant,
                'RapportEtudiant'
            );
            $this->notificationService->envoyerNotificationUtilisateur(
                $rapport['numero_carte_etudiant'],
                'RAPPORT_EDITION_REACTIVEE',
                "L'édition de votre rapport '{$rapport['libelle_rapport_etudiant']}' a été réactivée. Motif: {$motifActivation}"
            );
            return true;
        } catch (\Exception $e) {
            $this->rapportEtudiantModel->annulerTransaction();
            throw $e;
        }
    }

    public function listerRapportsParCriteres(array $criteres = [], array $colonnes = ['*'], string $operateurLogique = 'AND', ?string $orderBy = null, ?int $limit = null, ?int $offset = null): array
    {
        return $this->rapportEtudiantModel->trouverParCritere($criteres, $colonnes, $operateurLogique, $orderBy, $limit, $offset);
    }
}