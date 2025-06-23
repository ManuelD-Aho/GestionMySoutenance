<?php
namespace App\Backend\Model;

use PDO;

class Utilisateur extends BaseModel
{
    protected string $table = 'utilisateur';
    protected string|array $primaryKey = 'numero_utilisateur';

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }

    public function getProfil(): ?array
    {
        if (!isset($this->id_type_utilisateur) || !isset($this->numero_utilisateur)) return null;

        $model = null;
        switch ($this->id_type_utilisateur) {
            case 'TYPE_ETUD':
                $model = new Etudiant($this->db);
                break;
            case 'TYPE_ENS':
                $model = new Enseignant($this->db);
                break;
            case 'TYPE_PERS_ADMIN':
                $model = new PersonnelAdministratif($this->db);
                break;
        }
        return $model ? $model->trouverParIdentifiant($this->numero_utilisateur) : null;
    }

    public function getGroupe(): ?array
    {
        if (!isset($this->id_groupe_utilisateur)) return null;
        $groupeModel = new GroupeUtilisateur($this->db);
        return $groupeModel->trouverParIdentifiant($this->id_groupe_utilisateur);
    }

    public function getType(): ?array
    {
        if (!isset($this->id_type_utilisateur)) return null;
        $typeModel = new TypeUtilisateur($this->db);
        return $typeModel->trouverParIdentifiant($this->id_type_utilisateur);
    }

    public function getHistoriqueMotsDePasse(): array
    {
        if (!isset($this->numero_utilisateur)) return [];
        $histModel = new HistoriqueMotDePasse($this->db);
        return $histModel->trouverParCritere(['numero_utilisateur' => $this->numero_utilisateur]);
    }

    public function getPermissions(): array
    {
        if (!isset($this->id_groupe_utilisateur)) return [];
        $rattacherModel = new Rattacher($this->db);
        $rattachements = $rattacherModel->trouverParCritere(['id_groupe_utilisateur' => $this->id_groupe_utilisateur]);
        return array_column($rattachements, 'id_traitement');
    }

    public function aPermission(string $permissionCode): bool
    {
        return in_array($permissionCode, $this->getPermissions());
    }

    public function isActif(): bool
    {
        return isset($this->statut_compte) && $this->statut_compte === 'actif';
    }

    public function isBloque(): bool
    {
        if (!isset($this->statut_compte)) return false;
        if ($this->statut_compte === 'bloque') {
            if (isset($this->compte_bloque_jusqua) && new \DateTime() < new \DateTime($this->compte_bloque_jusqua)) {
                return true;
            }
        }
        return false;
    }

    public function isEmailValide(): bool
    {
        return isset($this->email_valide) ? (bool) $this->email_valide : false;
    }

    public function verifyPassword(string $password): bool
    {
        return isset($this->mot_de_passe) && password_verify($password, $this->mot_de_passe);
    }
}