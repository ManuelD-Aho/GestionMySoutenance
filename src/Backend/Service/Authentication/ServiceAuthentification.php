<?php

namespace App\Backend\Service\Authentication;

use App\Backend\Model\Enseignant;
use App\Backend\Model\Etudiant;
use App\Backend\Model\PersonnelAdministratif;
use App\Backend\Model\Utilisateur;
use PDO;

class ServiceAuthentification
{
    private Utilisateur $modeleUtilisateur;
    private Etudiant $modeleEtudiant;
    private Enseignant $modeleEnseignant;
    private PersonnelAdministratif $modelePersonnelAdministratif;
    private PDO $db;

    public function __construct(
        Utilisateur $modeleUtilisateur,
        Etudiant $modeleEtudiant,
        Enseignant $modeleEnseignant,
        PersonnelAdministratif $modelePersonnelAdministratif,
        PDO $db
    ) {
        $this->modeleUtilisateur = $modeleUtilisateur;
        $this->modeleEtudiant = $modeleEtudiant;
        $this->modeleEnseignant = $modeleEnseignant;
        $this->modelePersonnelAdministratif = $modelePersonnelAdministratif;
        $this->db = $db;
    }

    public function tenterConnexion(string $identifiantConnexion, string $motDePasse): ?array
    {
        $utilisateur = $this->modeleUtilisateur->trouverUnParCritere(['login_utilisateur' => $identifiantConnexion]);

        if ($utilisateur && $utilisateur['actif'] && password_verify($motDePasse, $utilisateur['mot_de_passe'])) {
            unset($utilisateur['mot_de_passe']);
            return $utilisateur;
        }
        return null;
    }

    public function etablirSessionUtilisateur(array $utilisateur): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['utilisateur_connecte'] = $utilisateur;
    }

    public function detruireSessionUtilisateur(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $parametres = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $parametres["path"],
                $parametres["domain"],
                $parametres["secure"],
                $parametres["httponly"]
            );
        }
        session_destroy();
    }

    public function estConnecte(): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return isset($_SESSION['utilisateur_connecte']);
    }

    public function recupererUtilisateurConnecte(): ?array
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return $_SESSION['utilisateur_connecte'] ?? null;
    }

    public function genererIdentifiantUtilisateurUnique(string $nom, string $prenom, string $prefixeRole): string
    {
        $baseIdentifiant = strtolower(substr($prenom, 0, 1) . '.' . preg_replace('/[^a-z0-9]/i', '', $nom));
        $identifiantPropose = $baseIdentifiant . '_' . $prefixeRole;
        $compteur = 0;
        $identifiantFinal = $identifiantPropose;

        while ($this->modeleUtilisateur->trouverUnParCritere(['login_utilisateur' => $identifiantFinal])) {
            $compteur++;
            $identifiantFinal = $identifiantPropose . $compteur;
        }
        return $identifiantFinal;
    }

    public function creerCompteUtilisateurComplet(array $donneesUtilisateur, string $motDePasseEnClair, string $typeProfil, array $donneesProfilSpecifique): ?string
    {
        $this->db->beginTransaction();
        try {
            $donneesUtilisateur['mot_de_passe'] = password_hash($motDePasseEnClair, PASSWORD_ARGON2ID);
            if (empty($donneesUtilisateur['date_creation'])) {
                $donneesUtilisateur['date_creation'] = date('Y-m-d H:i:s');
            }

            $numeroUtilisateur = $donneesUtilisateur['numero_utilisateur'];

            $resultatCreationUtilisateur = $this->modeleUtilisateur->creer($donneesUtilisateur);
            if (!$resultatCreationUtilisateur) {
                $this->db->rollBack();
                return null;
            }

            $donneesProfilSpecifique['numero_utilisateur'] = $numeroUtilisateur;

            $succesProfil = false;
            if ($typeProfil === 'etudiant') {
                $succesProfil = (bool)$this->modeleEtudiant->creer($donneesProfilSpecifique);
            } elseif ($typeProfil === 'enseignant') {
                $succesProfil = (bool)$this->modeleEnseignant->creer($donneesProfilSpecifique);
            } elseif ($typeProfil === 'personnel_administratif') {
                $succesProfil = (bool)$this->modelePersonnelAdministratif->creer($donneesProfilSpecifique);
            }

            if ($succesProfil) {
                $this->db->commit();
                return $numeroUtilisateur;
            } else {
                $this->db->rollBack();
                return null;
            }
        } catch (\Exception $e) {
            $this->db->rollBack();
            return null;
        }
    }

    public function reinitialiserMotDePasse(string $numeroUtilisateur, string $nouveauMotDePasseEnClair): bool
    {
        $motDePasseHache = password_hash($nouveauMotDePasseEnClair, PASSWORD_ARGON2ID);
        return $this->modeleUtilisateur->mettreAJourParIdentifiant($numeroUtilisateur, ['mot_de_passe' => $motDePasseHache]);
    }
}