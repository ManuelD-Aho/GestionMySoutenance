<?php

namespace App\Backend\Service\SupervisionAdmin;

use App\Backend\Model\CompteRendu;
use App\Backend\Model\Enregistrer;
use App\Backend\Model\Pister;
use App\Backend\Model\RapportEtudiant;
use PDO;
use DateTime;

class ServiceSupervisionAdmin implements ServiceSupervisionAdminInterface
{
    private RapportEtudiant $modeleRapportEtudiant;
    private Enregistrer $modeleEnregistrer;
    private Pister $modelePister;
    private CompteRendu $modeleCompteRendu;
    private PDO $db;

    public function __construct(
        RapportEtudiant $modeleRapportEtudiant,
        Enregistrer $modeleEnregistrer,
        Pister $modelePister,
        CompteRendu $modeleCompteRendu,
        PDO $db
    ) {
        $this->modeleRapportEtudiant = $modeleRapportEtudiant;
        $this->modeleEnregistrer = $modeleEnregistrer;
        $this->modelePister = $modelePister;
        $this->modeleCompteRendu = $modeleCompteRendu;
        $this->db = $db;
    }
    public function enregistrerAction(
        string $loginUtilisateur,
        string $codeAction,
        DateTime $dateAction,
        string $adresseIp,
        string $userAgent,
        string $contexteEntite,
        ?string $idEntite,
        ?array $details
    ): bool {
        $donnees = [
            'numero_utilisateur_trigger' => $loginUtilisateur, // Adapter au nom de colonne de la table 'enregistrer'
            'code_action' => $codeAction,                   // Adapter
            'date_action' => $dateAction->format('Y-m-d H:i:s'),
            'adresse_ip_action' => $adresseIp,             // Adapter
            'user_agent_action' => $userAgent,             // Adapter
            'contexte_entite_action' => $contexteEntite,   // Adapter
            'id_entite_concernee_action' => $idEntite,     // Adapter
            'details_action' => json_encode($details)      // Adapter
        ];
        // Supposons que votre modèle Enregistrer a une méthode 'creer'
        $result = $this->modeleEnregistrer->creer($donnees); // La méthode creer de BaseModel retourne string|bool
        return is_string($result) || $result === true;
    }

    public function obtenirStatistiquesGlobalesRapports(): array
    {
        $sql = "SELECT sr.libelle, COUNT(re.id_rapport_etudiant) as nombre
                FROM statut_rapport_ref sr
                LEFT JOIN rapport_etudiant re ON sr.id_statut_rapport = re.id_statut_rapport
                GROUP BY sr.id_statut_rapport, sr.libelle
                ORDER BY sr.id_statut_rapport";
        $declaration = $this->modeleRapportEtudiant->executerRequete($sql);
        return $declaration->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function consulterJournauxActionsUtilisateurs(array $filtres = [], int $limite = 50, int $page = 1): array
    {
        $offset = ($page - 1) * $limite;
        $conditions = [];
        $parametres = [];

        if (!empty($filtres['numero_utilisateur'])) {
            $conditions[] = "e.numero_utilisateur = :num_user";
            $parametres[':num_user'] = $filtres['numero_utilisateur'];
        }
        if (!empty($filtres['id_action'])) {
            $conditions[] = "e.id_action = :id_action";
            $parametres[':id_action'] = $filtres['id_action'];
        }
        if (!empty($filtres['date_debut'])) {
            $conditions[] = "e.date_action >= :date_debut";
            $parametres[':date_debut'] = $filtres['date_debut'];
        }
        if (!empty($filtres['date_fin'])) {
            $conditions[] = "e.date_action <= :date_fin";
            $parametres[':date_fin'] = $filtres['date_fin'];
        }

        $sqlWhere = !empty($conditions) ? "WHERE " . implode(" AND ", $conditions) : "";
        $sql = "SELECT e.*, u.login_utilisateur, a.lib_action 
                FROM enregistrer e 
                JOIN utilisateur u ON e.numero_utilisateur = u.numero_utilisateur
                JOIN action a ON e.id_action = a.id_action
                {$sqlWhere}
                ORDER BY e.date_action DESC 
                LIMIT :limite OFFSET :offset";

        $declaration = $this->db->prepare($sql);
        $declaration->bindParam(':limite', $limite, PDO::PARAM_INT);
        $declaration->bindParam(':offset', $offset, PDO::PARAM_INT);
        foreach($parametres as $cle => $valeur) {
            $declaration->bindValue($cle, $valeur);
        }
        $declaration->execute();
        return $declaration->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function consulterTracesAccesFonctionnalites(array $filtres = [], int $limite = 50, int $page = 1): array
    {
        $offset = ($page - 1) * $limite;
        $conditions = [];
        $parametres = [];

        if (!empty($filtres['numero_utilisateur'])) {
            $conditions[] = "p.numero_utilisateur = :num_user";
            $parametres[':num_user'] = $filtres['numero_utilisateur'];
        }
        if (!empty($filtres['id_traitement'])) {
            $conditions[] = "p.id_traitement = :id_traitement";
            $parametres[':id_traitement'] = $filtres['id_traitement'];
        }
        if (!empty($filtres['date_debut'])) {
            $conditions[] = "p.date_pister >= :date_debut";
            $parametres[':date_debut'] = $filtres['date_debut'];
        }
        if (!empty($filtres['date_fin'])) {
            $conditions[] = "p.date_pister <= :date_fin";
            $parametres[':date_fin'] = $filtres['date_fin'];
        }

        $sqlWhere = !empty($conditions) ? "WHERE " . implode(" AND ", $conditions) : "";
        $sql = "SELECT p.*, u.login_utilisateur, t.lib_trait 
                FROM pister p 
                JOIN utilisateur u ON p.numero_utilisateur = u.numero_utilisateur
                JOIN traitement t ON p.id_traitement = t.id_traitement
                {$sqlWhere}
                ORDER BY p.date_pister DESC 
                LIMIT :limite OFFSET :offset";

        $declaration = $this->db->prepare($sql);
        $declaration->bindParam(':limite', $limite, PDO::PARAM_INT);
        $declaration->bindParam(':offset', $offset, PDO::PARAM_INT);
        foreach($parametres as $cle => $valeur) {
            $declaration->bindValue($cle, $valeur);
        }
        $declaration->execute();
        return $declaration->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function listerPvEligiblesArchivage(array $criteres = []): array
    {
        $conditions = ["cr.id_statut_pv = 3"]; // 3: Validé
        $parametres = [];

        if (!empty($criteres['date_validation_avant'])) {
            $conditions[] = "vp.date_validation < :date_validation_avant";
            $parametres[':date_validation_avant'] = $criteres['date_validation_avant'];
        }

        $sqlWhere = "WHERE " . implode(" AND ", $conditions);
        $sql = "SELECT cr.* 
                FROM compte_rendu cr
                LEFT JOIN validation_pv vp ON cr.id_compte_rendu = vp.id_compte_rendu 
                {$sqlWhere}
                GROUP BY cr.id_compte_rendu
                ORDER BY MAX(vp.date_validation) ASC"; // Order by the latest validation date if multiple validations exist

        $declaration = $this->modeleCompteRendu->executerRequete($sql, $parametres);
        return $declaration->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}