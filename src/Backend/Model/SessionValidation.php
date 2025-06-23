<?php
namespace App\Backend\Model;

use PDO;

class SessionValidation extends BaseModel
{
    protected string $table = 'session_validation';
    protected string|array $primaryKey = 'id_session';

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }

    public function getPresident(): ?array
    {
        if (!isset($this->id_president_session)) return null;
        $enseignantModel = new Enseignant($this->db);
        return $enseignantModel->trouverParIdentifiant($this->id_president_session);
    }

    public function getRapports(): array
    {
        if (!isset($this->id_session)) return [];
        $sessionRapportModel = new SessionRapport($this->db);
        return $sessionRapportModel->trouverParCritere(['id_session' => $this->id_session]);
    }

    public function tousRapportsEvalues(): bool
    {
        $rapports = $this->getRapports();
        if (empty($rapports)) return true;

        $rapportModel = new RapportEtudiant($this->db);
        foreach ($rapports as $sr) {
            $rapport = $rapportModel->trouverParIdentifiant($sr['id_rapport_etudiant']);
            if ($rapport && in_array($rapport['id_statut_rapport'], ['RAP_EN_COMM', 'RAP_SOUMIS'])) {
                return false;
            }
        }
        return true;
    }
}