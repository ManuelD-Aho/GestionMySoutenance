<?php

namespace App\Backend\Service\SupervisionAdmin;

use App\Backend\Exception\ElementNonTrouveException;
use App\Backend\Model\Action;
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
    private Action $modeleAction;

    public function __construct(
        RapportEtudiant $modeleRapportEtudiant,
        Enregistrer $modeleEnregistrer,
        Pister $modelePister,
        CompteRendu $modeleCompteRendu,
        Action $modeleAction
    ) {
        $this->modeleRapportEtudiant = $modeleRapportEtudiant;
        $this->modeleEnregistrer = $modeleEnregistrer;
        $this->modelePister = $modelePister;
        $this->modeleCompteRendu = $modeleCompteRendu;
        $this->modeleAction = $modeleAction;
    }

    public function recupererOuCreerIdActionParLibelle(string $libelleAction): int
    {
        try {
            $action = $this->modeleAction->findBy(['libelle_action' => $libelleAction]);
            if ($action) {
                return (int)$action['id_action'];
            }
        } catch (ElementNonTrouveException $e) {
            // L'action n'existe pas, on continue pour la créer
        }

        $idAction = $this->modeleAction->create(['libelle_action' => $libelleAction]);
        return (int)$idAction;
    }

    // CORRECTION : La signature est maintenant identique à celle de l'interface.
    public function enregistrerAction(
        string $loginUtilisateur,
        string $codeAction, // Le nom et le type correspondent à l'interface
        DateTime $dateAction, // Le paramètre de date est restauré
        string $adresseIp,
        string $userAgent,
        string $contexteEntite,
        ?string $idEntite,
        ?array $details
    ): bool {
        // On utilise le paramètre $codeAction (qui est le libellé) pour récupérer l'ID
        $idAction = $this->recupererOuCreerIdActionParLibelle($codeAction);

        $donnees = [
            'numero_utilisateur' => $loginUtilisateur,
            'id_action' => $idAction,
            'date_action' => $dateAction->format('Y-m-d H:i:s'), // On utilise l'objet $dateAction fourni
            'adresse_ip' => $adresseIp,
            'user_agent' => $userAgent,
            'contexte_entite' => $contexteEntite,
            'id_entite_concernee' => $idEntite,
            'details' => json_encode($details)
        ];

        $result = $this->modeleEnregistrer->create($donnees);
        return is_string($result) || $result === true;
    }

    public function obtenirStatistiquesGlobalesRapports(): array
    {
        $sql = "SELECT sr.libelle_statut_rapport, COUNT(re.id_rapport_etudiant) as nombre
                FROM statut_rapport_ref sr
                LEFT JOIN rapport_etudiant re ON sr.id_statut_rapport = re.id_statut_rapport
                GROUP BY sr.id_statut_rapport, sr.libelle_statut_rapport
                ORDER BY sr.id_statut_rapport";
        $declaration = $this->modeleRapportEtudiant->executerRequete($sql);
        return $declaration->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function consulterJournauxActionsUtilisateurs(array $filtres = [], int $limite = 50, int $page = 1): array
    {
        $offset = ($page - 1) * $limite;
        $conditions = [];
        $parametres = [':limite' => $limite, ':offset' => $offset];

        if (!empty($filtres['numero_utilisateur'])) {
            $conditions[] = "e.numero_utilisateur = :num_user";
            $parametres[':num_user'] = $filtres['numero_utilisateur'];
        }

        $sqlWhere = !empty($conditions) ? "WHERE " . implode(" AND ", $conditions) : "";

        $sql = "SELECT e.*, u.login_utilisateur, a.libelle_action 
                FROM enregistrer e 
                JOIN utilisateur u ON e.numero_utilisateur = u.numero_utilisateur
                JOIN action a ON e.id_action = a.id_action
                {$sqlWhere}
                ORDER BY e.date_action DESC 
                LIMIT :limite OFFSET :offset";

        $declaration = $this->modeleEnregistrer->executerRequete($sql, $parametres);
        return $declaration->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function consulterTracesAccesFonctionnalites(array $filtres = [], int $limite = 50, int $page = 1): array
    {
        $offset = ($page - 1) * $limite;
        $conditions = [];
        $parametres = [':limite' => $limite, ':offset' => $offset];

        $sqlWhere = !empty($conditions) ? "WHERE " . implode(" AND ", $conditions) : "";

        $sql = "SELECT p.*, u.login_utilisateur, t.libelle_traitement 
                FROM pister p 
                JOIN utilisateur u ON p.numero_utilisateur = u.numero_utilisateur
                JOIN traitement t ON p.id_traitement = t.id_traitement
                {$sqlWhere}
                ORDER BY p.date_pister DESC 
                LIMIT :limite OFFSET :offset";

        $declaration = $this->modelePister->executerRequete($sql, $parametres);
        return $declaration->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function listerPvEligiblesArchivage(array $criteres = []): array
    {
        $conditions = ["cr.id_statut_pv = 3"];
        $parametres = [];

        if (!empty($criteres['date_validation_avant'])) {
            $conditions[] = "vp.date_validation < :date_validation_avant";
            $parametres[':date_validation_avant'] = $criteres['date_validation_avant'];
        }

        $sqlWhere = "WHERE " . implode(" AND ", $conditions);

        $sql = "SELECT cr.* FROM compte_rendu cr
                INNER JOIN validation_pv vp ON cr.id_compte_rendu = vp.id_compte_rendu 
                {$sqlWhere}
                GROUP BY cr.id_compte_rendu
                ORDER BY MAX(vp.date_validation) ASC";

        $declaration = $this->modeleCompteRendu->executerRequete($sql, $parametres);
        return $declaration->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}