<?php
namespace App\Backend\Service\Rapport;

use PDO;
use App\Backend\Model\RapportEtudiant;
use App\Backend\Model\SectionRapport;
use App\Backend\Model\Utilisateur;
use App\Backend\Service\Notification\ServiceNotificationInterface;
use App\Backend\Service\SupervisionAdmin\ServiceSupervisionAdminInterface;
use App\Backend\Service\IdentifiantGenerator\IdentifiantGeneratorInterface;
use App\Backend\Exception\ElementNonTrouveException;
use App\Backend\Exception\OperationImpossibleException;

class ServiceRapport implements ServiceRapportInterface
{
    private RapportEtudiant $rapportEtudiantModel;
    private SectionRapport $sectionRapportModel;
    private Utilisateur $utilisateurModel;
    private ServiceNotificationInterface $notificationService;
    private ServiceSupervisionAdminInterface $supervisionService;
    private IdentifiantGeneratorInterface $idGenerator;

    public function __construct(PDO $db, ServiceNotificationInterface $notificationService, ServiceSupervisionAdminInterface $supervisionService, IdentifiantGeneratorInterface $idGenerator)
    {
        $this->rapportEtudiantModel = new RapportEtudiant($db);
        $this->sectionRapportModel = new SectionRapport($db);
        $this->utilisateurModel = new Utilisateur($db);
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
            $existingRapport = $this->rapportEtudiantModel->trouverUnParCritere(['numero_carte_etudiant' => $numeroCarteEtudiant, 'id_statut_rapport' => 'RAP_BROUILLON']);

            $idRapport = $existingRapport['id_rapport_etudiant'] ?? null;
            if ($idRapport) {
                $metadonnees['date_derniere_modif'] = date('Y-m-d H:i:s');
                $this->rapportEtudiantModel->mettreAJourParIdentifiant($idRapport, $metadonnees);
            } else {
                $idRapport = $this->idGenerator->generate('rapport_etudiant');
                $metadonnees['id_rapport_etudiant'] = $idRapport;
                $metadonnees['numero_carte_etudiant'] = $numeroCarteEtudiant;
                $metadonnees['id_statut_rapport'] = 'RAP_BROUILLON';
                $metadonnees['date_derniere_modif'] = date('Y-m-d H:i:s');
                $this->rapportEtudiantModel->creer($metadonnees);
            }

            foreach ($sectionsContenu as $titreSection => $contenu) {
                $sectionData = ['contenu_section' => $contenu];
                $existingSection = $this->sectionRapportModel->trouverUnParCritere(['id_rapport_etudiant' => $idRapport, 'titre_section' => $titreSection]);
                if ($existingSection) {
                    $this->sectionRapportModel->mettreAJourParIdentifiant($existingSection['id_section'], $sectionData);
                } else {
                    $sectionData['id_section'] = $this->idGenerator->generate('rapport_modele_section');
                    $sectionData['id_rapport_etudiant'] = $idRapport;
                    $sectionData['titre_section'] = $titreSection;
                    $this->sectionRapportModel->creer($sectionData);
                }
            }

            $this->rapportEtudiantModel->validerTransaction();
            return $idRapport;
        } catch (\Exception $e) {
            $this->rapportEtudiantModel->annulerTransaction();
            throw new OperationImpossibleException("Erreur lors de la sauvegarde du brouillon: " . $e->getMessage());
        }
    }

    public function soumettreRapportPourVerification(string $idRapportEtudiant): bool
    {
        $rapport = $this->rapportEtudiantModel->trouverParIdentifiant($idRapportEtudiant);
        if (!$rapport) throw new ElementNonTrouveException("Rapport non trouvé.");
        if (!in_array($rapport['id_statut_rapport'], ['RAP_BROUILLON', 'RAP_NON_CONF', 'RAP_CORRECT'])) {
            throw new OperationImpossibleException("Le rapport ne peut être soumis que s'il est en brouillon ou en correction.");
        }

        $success = $this->mettreAJourStatutRapport($idRapportEtudiant, 'RAP_SOUMIS');
        if ($success) {
            $this->notificationService->send($rapport['numero_carte_etudiant'], 'RAPPORT_SOUMIS');
            $this->notificationService->sendToGroup('GRP_AGENT_CONFORMITE', 'NOUVEAU_RAPPORT_A_VERIFIER', ['rapport_id' => $idRapportEtudiant]);
        }
        return $success;
    }

    public function enregistrerCorrectionsSoumises(string $idRapportEtudiant, array $sectionsContenuCorriges, string $numeroUtilisateurUpload, ?string $noteExplicative = null): bool
    {
        $rapport = $this->rapportEtudiantModel->trouverParIdentifiant($idRapportEtudiant);
        if (!$rapport || $rapport['numero_carte_etudiant'] !== $numeroUtilisateurUpload) {
            throw new OperationImpossibleException("Action non autorisée.");
        }
        if (!in_array($rapport['id_statut_rapport'], ['RAP_NON_CONF', 'RAP_CORRECT'])) {
            throw new OperationImpossibleException("Le rapport n'est pas en attente de corrections.");
        }

        $this->rapportEtudiantModel->commencerTransaction();
        try {
            foreach ($sectionsContenuCorriges as $titreSection => $contenu) {
                $this->sectionRapportModel->mettreAJourParClesInternes(
                    ['id_rapport_etudiant' => $idRapportEtudiant, 'titre_section' => $titreSection],
                    ['contenu_section' => $contenu]
                );
            }
            $this->mettreAJourStatutRapport($idRapportEtudiant, 'RAP_SOUMIS');
            $this->supervisionService->enregistrerAction($numeroUtilisateurUpload, 'SOUMISSION_CORRECTIONS', "Corrections soumises pour rapport {$idRapportEtudiant}", $idRapportEtudiant, 'RapportEtudiant', ['note' => $noteExplicative]);
            $this->rapportEtudiantModel->validerTransaction();
            return true;
        } catch (\Exception $e) {
            $this->rapportEtudiantModel->annulerTransaction();
            throw new OperationImpossibleException("Erreur lors de la soumission des corrections: " . $e->getMessage());
        }
    }

    public function recupererInformationsRapportComplet(string $idRapportEtudiant): ?array
    {
        $rapport = $this->rapportEtudiantModel->trouverParIdentifiant($idRapportEtudiant);
        if (!$rapport) return null;
        $rapport['sections'] = $this->sectionRapportModel->trouverParCritere(['id_rapport_etudiant' => $idRapportEtudiant]);
        return $rapport;
    }

    public function mettreAJourStatutRapport(string $idRapportEtudiant, string $newStatutId): bool
    {
        return $this->rapportEtudiantModel->mettreAJourParIdentifiant($idRapportEtudiant, ['id_statut_rapport' => $newStatutId, 'date_derniere_modif' => date('Y-m-d H:i:s')]);
    }

    public function reactiverEditionRapport(string $idRapportEtudiant, string $motifActivation = 'Reprise demandée'): bool
    {
        $rapport = $this->rapportEtudiantModel->trouverParIdentifiant($idRapportEtudiant);
        if (!$rapport) throw new ElementNonTrouveException("Rapport non trouvé.");
        if (!in_array($rapport['id_statut_rapport'], ['RAP_REFUSE', 'RAP_CORRECT'])) {
            throw new OperationImpossibleException("Le rapport n'est pas dans un état permettant la réactivation.");
        }
        return $this->mettreAJourStatutRapport($idRapportEtudiant, 'RAP_BROUILLON');
    }

    public function listerRapportsParCriteres(array $criteres = [], array $colonnes = ['*'], string $operateurLogique = 'AND', ?string $orderBy = null, ?int $limit = null, ?int $offset = null): array
    {
        return $this->rapportEtudiantModel->trouverParCritere($criteres, $colonnes, $operateurLogique, $orderBy, $limit, $offset);
    }
}