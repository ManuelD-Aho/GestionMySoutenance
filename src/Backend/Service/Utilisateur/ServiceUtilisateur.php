<?php

namespace App\Backend\Service\Utilisateur;

use PDO;
use App\Backend\Model\Utilisateur;
use App\Backend\Model\Etudiant;
use App\Backend\Model\Enseignant;
use App\Backend\Model\PersonnelAdministratif;
use App\Backend\Model\Delegation;
use App\Backend\Service\Authentication\ServiceAuthenticationInterface;
use App\Backend\Service\SupervisionAdmin\ServiceSupervisionAdminInterface;
use App\Backend\Service\IdentifiantGenerator\IdentifiantGeneratorInterface;
use App\Backend\Service\Notification\ServiceNotificationInterface;
use App\Backend\Exception\ElementNonTrouveException;
use App\Backend\Exception\DoublonException;
use App\Backend\Exception\ValidationException;
use App\Backend\Exception\OperationImpossibleException;

class ServiceUtilisateur implements ServiceUtilisateurInterface
{
    private PDO $db;
    private Utilisateur $utilisateurModel;
    private Etudiant $etudiantModel;
    private Enseignant $enseignantModel;
    private PersonnelAdministratif $personnelAdminModel;
    private Delegation $delegationModel;
    private ServiceAuthenticationInterface $authService;
    private ServiceSupervisionAdminInterface $supervisionService;
    private IdentifiantGeneratorInterface $idGenerator;
    private ServiceNotificationInterface $notificationService;

    public function __construct(
        PDO $db,
        Utilisateur $utilisateurModel,
        Etudiant $etudiantModel,
        Enseignant $enseignantModel,
        PersonnelAdministratif $personnelAdminModel,
        Delegation $delegationModel,
        ServiceAuthenticationInterface $authService,
        ServiceSupervisionAdminInterface $supervisionService,
        IdentifiantGeneratorInterface $idGenerator,
        ServiceNotificationInterface $notificationService
    ) {
        $this->db = $db;
        $this->utilisateurModel = $utilisateurModel;
        $this->etudiantModel = $etudiantModel;
        $this->enseignantModel = $enseignantModel;
        $this->personnelAdminModel = $personnelAdminModel;
        $this->delegationModel = $delegationModel;
        $this->authService = $authService;
        $this->supervisionService = $supervisionService;
        $this->idGenerator = $idGenerator;
        $this->notificationService = $notificationService;
    }

    public function creerUtilisateurComplet(array $donneesUtilisateur, array $donneesProfil, string $typeProfilCode): string
    {
        try {
            $this->db->beginTransaction();

            $numeroUtilisateur = $this->authService->creerCompteUtilisateurComplet(
                $donneesUtilisateur,
                $donneesProfil,
                $typeProfilCode,
                true
            );

            $this->supervisionService->enregistrerAction(
                $_SESSION['numero_utilisateur'] ?? 'SYSTEM',
                'CREATION_UTILISATEUR',
                "Création d'un nouvel utilisateur {$typeProfilCode}",
                'utilisateur',
                $numeroUtilisateur
            );

            $this->db->commit();
            return $numeroUtilisateur;

        } catch (\Exception $e) {
            $this->db->rollBack();
            throw new OperationImpossibleException("Impossible de créer l'utilisateur: " . $e->getMessage());
        }
    }

    public function mettreAJourUtilisateur(string $numeroUtilisateur, array $donneesUtilisateur): bool
    {
        try {
            $utilisateurExistant = $this->utilisateurModel->trouverParNumero($numeroUtilisateur);
            if (!$utilisateurExistant) {
                throw new ElementNonTrouveException("Utilisateur non trouvé: {$numeroUtilisateur}");
            }

            $this->db->beginTransaction();

            $result = $this->utilisateurModel->mettreAJour($numeroUtilisateur, $donneesUtilisateur);

            if ($result) {
                $this->supervisionService->enregistrerAction(
                    $_SESSION['numero_utilisateur'] ?? 'SYSTEM',
                    'MODIFICATION_UTILISATEUR',
                    "Modification des données de l'utilisateur",
                    'utilisateur',
                    $numeroUtilisateur,
                    $donneesUtilisateur
                );

                // Notifier l'utilisateur si son profil a été modifié par un admin
                if (($_SESSION['numero_utilisateur'] ?? null) !== $numeroUtilisateur) {
                    $this->notificationService->envoyerNotificationUtilisateur(
                        $numeroUtilisateur,
                        'PROFIL_MODIFIE_ADMIN',
                        'Votre profil a été modifié par un administrateur',
                        ['modificateur' => $_SESSION['numero_utilisateur'] ?? 'SYSTEM']
                    );
                }
            }

            $this->db->commit();
            return $result;

        } catch (\Exception $e) {
            $this->db->rollBack();
            throw new OperationImpossibleException("Impossible de mettre à jour l'utilisateur: " . $e->getMessage());
        }
    }

    public function obtenirUtilisateurComplet(string $numeroUtilisateur): ?array
    {
        $utilisateur = $this->utilisateurModel->trouverParNumero($numeroUtilisateur);
        
        if (!$utilisateur) {
            return null;
        }

        // Récupérer les données du profil spécifique
        $profil = null;
        switch ($utilisateur['code_type_utilisateur']) {
            case 'ETUDIANT':
                $profil = $this->etudiantModel->trouverParNumeroUtilisateur($numeroUtilisateur);
                break;
            case 'ENSEIGNANT':
                $profil = $this->enseignantModel->trouverParNumeroUtilisateur($numeroUtilisateur);
                break;
            case 'PERSONNEL_ADMIN':
                $profil = $this->personnelAdminModel->trouverParNumeroUtilisateur($numeroUtilisateur);
                break;
        }

        return [
            'utilisateur' => $utilisateur,
            'profil' => $profil,
            'delegations_actives' => $this->obtenirDelegationsActives($numeroUtilisateur)
        ];
    }

    public function listerUtilisateurs(array $criteres = [], int $page = 1, int $elementsParPage = 20): array
    {
        $offset = ($page - 1) * $elementsParPage;
        
        $sql = "SELECT u.*, 
                CASE 
                    WHEN u.code_type_utilisateur = 'ETUDIANT' THEN e.nom_etudiant
                    WHEN u.code_type_utilisateur = 'ENSEIGNANT' THEN ens.nom_enseignant
                    WHEN u.code_type_utilisateur = 'PERSONNEL_ADMIN' THEN pa.nom_personnel
                    ELSE NULL
                END as nom_complet,
                CASE 
                    WHEN u.code_type_utilisateur = 'ETUDIANT' THEN e.prenom_etudiant
                    WHEN u.code_type_utilisateur = 'ENSEIGNANT' THEN ens.prenom_enseignant
                    WHEN u.code_type_utilisateur = 'PERSONNEL_ADMIN' THEN pa.prenom_personnel
                    ELSE NULL
                END as prenom_complet
                FROM utilisateur u
                LEFT JOIN etudiant e ON u.numero_utilisateur = e.numero_utilisateur
                LEFT JOIN enseignant ens ON u.numero_utilisateur = ens.numero_utilisateur
                LEFT JOIN personnel_administratif pa ON u.numero_utilisateur = pa.numero_utilisateur
                WHERE 1=1";

        $params = [];

        // Appliquer les critères
        if (!empty($criteres['type_utilisateur'])) {
            $sql .= " AND u.code_type_utilisateur = ?";
            $params[] = $criteres['type_utilisateur'];
        }

        if (!empty($criteres['statut'])) {
            $sql .= " AND u.statut_compte = ?";
            $params[] = $criteres['statut'];
        }

        if (!empty($criteres['recherche'])) {
            $sql .= " AND (u.login LIKE ? OR u.email_principal LIKE ? OR 
                     e.nom_etudiant LIKE ? OR e.prenom_etudiant LIKE ? OR
                     ens.nom_enseignant LIKE ? OR ens.prenom_enseignant LIKE ? OR
                     pa.nom_personnel LIKE ? OR pa.prenom_personnel LIKE ?)";
            $terme = '%' . $criteres['recherche'] . '%';
            $params = array_merge($params, array_fill(0, 8, $terme));
        }

        // Compter le total
        $countSql = "SELECT COUNT(*) as total FROM ($sql) as count_query";
        $stmt = $this->db->prepare($countSql);
        $stmt->execute($params);
        $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Récupérer les données paginées
        $sql .= " ORDER BY u.date_creation DESC LIMIT ? OFFSET ?";
        $params[] = $elementsParPage;
        $params[] = $offset;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $utilisateurs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'utilisateurs' => $utilisateurs,
            'pagination' => [
                'page_actuelle' => $page,
                'elements_par_page' => $elementsParPage,
                'total_elements' => $total,
                'total_pages' => ceil($total / $elementsParPage)
            ]
        ];
    }

    public function changerStatutUtilisateur(string $numeroUtilisateur, string $statut, ?string $raison = null): bool
    {
        $statutsValides = ['ACTIF', 'INACTIF', 'BLOQUE', 'SUSPENDU'];
        if (!in_array($statut, $statutsValides)) {
            throw new ValidationException("Statut non valide: {$statut}");
        }

        try {
            $this->db->beginTransaction();

            $result = $this->authService->changerStatutDuCompte($numeroUtilisateur, $statut, $raison);

            if ($result) {
                $this->supervisionService->enregistrerAction(
                    $_SESSION['numero_utilisateur'] ?? 'SYSTEM',
                    'CHANGEMENT_STATUT_UTILISATEUR',
                    "Changement de statut vers: {$statut}",
                    'utilisateur',
                    $numeroUtilisateur,
                    ['nouveau_statut' => $statut, 'raison' => $raison]
                );

                // Notifier l'utilisateur du changement de statut
                $this->notificationService->envoyerNotificationUtilisateur(
                    $numeroUtilisateur,
                    'CHANGEMENT_STATUT_COMPTE',
                    "Votre compte a été {$statut}",
                    ['statut' => $statut, 'raison' => $raison]
                );
            }

            $this->db->commit();
            return $result;

        } catch (\Exception $e) {
            $this->db->rollBack();
            throw new OperationImpossibleException("Impossible de changer le statut: " . $e->getMessage());
        }
    }

    public function supprimerUtilisateur(string $numeroUtilisateur, string $raison): bool
    {
        try {
            $this->db->beginTransaction();

            $result = $this->authService->supprimerUtilisateur($numeroUtilisateur);

            if ($result) {
                $this->supervisionService->enregistrerAction(
                    $_SESSION['numero_utilisateur'] ?? 'SYSTEM',
                    'SUPPRESSION_UTILISATEUR',
                    "Suppression de l'utilisateur - Raison: {$raison}",
                    'utilisateur',
                    $numeroUtilisateur,
                    ['raison' => $raison]
                );
            }

            $this->db->commit();
            return $result;

        } catch (\Exception $e) {
            $this->db->rollBack();
            throw new OperationImpossibleException("Impossible de supprimer l'utilisateur: " . $e->getMessage());
        }
    }

    public function rechercherUtilisateurs(string $terme, array $filtres = []): array
    {
        $criteres = array_merge($filtres, ['recherche' => $terme]);
        return $this->listerUtilisateurs($criteres, 1, 50);
    }

    public function importerUtilisateursEnMasse(string $cheminFichier, string $format): array
    {
        // Cette méthode sera implémentée selon les besoins spécifiques
        // Pour l'instant, retourner un résultat basique
        return [
            'total_traites' => 0,
            'succes' => 0,
            'erreurs' => 0,
            'details' => []
        ];
    }

    public function exporterUtilisateurs(array $criteres, string $format): string
    {
        // Cette méthode sera implémentée selon les besoins spécifiques
        // Pour l'instant, retourner un chemin factice
        return '/tmp/export_utilisateurs_' . date('Y-m-d_H-i-s') . '.' . strtolower($format);
    }

    public function obtenirHistoriqueUtilisateur(string $numeroUtilisateur, int $limite = 50): array
    {
        $sql = "SELECT * FROM historique_actions 
                WHERE numero_utilisateur_concerne = ? 
                ORDER BY date_action DESC 
                LIMIT ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$numeroUtilisateur, $limite]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function gererDelegation(string $numeroUtilisateur, string $action, array $donneesDelegation): bool
    {
        try {
            $this->db->beginTransaction();

            switch ($action) {
                case 'CREER':
                    $idDelegation = $this->idGenerator->genererProchainId('delegation');
                    $donneesDelegation['id_delegation'] = $idDelegation;
                    $donneesDelegation['numero_utilisateur_delegue'] = $numeroUtilisateur;
                    $result = $this->delegationModel->creer($donneesDelegation);
                    break;

                case 'MODIFIER':
                    $result = $this->delegationModel->mettreAJour($donneesDelegation['id_delegation'], $donneesDelegation);
                    break;

                case 'SUPPRIMER':
                    $result = $this->delegationModel->supprimer($donneesDelegation['id_delegation']);
                    break;

                default:
                    throw new ValidationException("Action non valide: {$action}");
            }

            if ($result) {
                $this->supervisionService->enregistrerAction(
                    $_SESSION['numero_utilisateur'] ?? 'SYSTEM',
                    "DELEGATION_{$action}",
                    "Gestion de délégation pour l'utilisateur",
                    'delegation',
                    $donneesDelegation['id_delegation'] ?? null,
                    $donneesDelegation
                );
            }

            $this->db->commit();
            return $result;

        } catch (\Exception $e) {
            $this->db->rollBack();
            throw new OperationImpossibleException("Impossible de gérer la délégation: " . $e->getMessage());
        }
    }

    public function aDelegationActive(string $numeroUtilisateur, string $typeDelegation): bool
    {
        $sql = "SELECT COUNT(*) as count FROM delegation 
                WHERE numero_utilisateur_delegue = ? 
                AND type_delegation = ? 
                AND date_debut <= NOW() 
                AND (date_fin IS NULL OR date_fin >= NOW()) 
                AND statut_delegation = 'ACTIVE'";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$numeroUtilisateur, $typeDelegation]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['count'] > 0;
    }

    private function obtenirDelegationsActives(string $numeroUtilisateur): array
    {
        $sql = "SELECT * FROM delegation 
                WHERE numero_utilisateur_delegue = ? 
                AND date_debut <= NOW() 
                AND (date_fin IS NULL OR date_fin >= NOW()) 
                AND statut_delegation = 'ACTIVE'";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$numeroUtilisateur]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}