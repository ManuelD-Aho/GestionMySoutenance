<?php
namespace App\Backend\Model;

use PDO;

class SessionRapport extends BaseModel
{
    protected string $table = 'session_rapport';
    // La clé primaire est composite: id_session (VARCHAR(50)) et id_rapport_etudiant (VARCHAR(50))
    protected string|array $primaryKey = ['id_session', 'id_rapport_etudiant'];

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }

    /**
     * Trouve tous les rapports rattachés à une session donnée.
     * @param string $idSession L'ID de la session de validation.
     * @param array $colonnes Les colonnes à sélectionner.
     * @return array Liste des rapports dans la session.
     */
    public function trouverRapportsDansSession(string $idSession, array $colonnes = ['*']): array
    {
        return $this->trouverParCritere(['id_session' => $idSession], $colonnes);
    }

    /**
     * Rattache un rapport à une session.
     * @param string $idSession L'ID de la session.
     * @param string $idRapportEtudiant L'ID du rapport étudiant.
     * @param array $donnees Données supplémentaires pour la liaison (date_ajout est auto-générée).
     * @return string|bool L'ID composite (première partie) si succès, false sinon, ou true si `creer` ne renvoie pas l'ID.
     */
    public function rattacherRapport(string $idSession, string $idRapportEtudiant, array $donnees = []): string|bool
    {
        $data = array_merge($donnees, [
            'id_session' => $idSession,
            'id_rapport_etudiant' => $idRapportEtudiant
        ]);
        // Pour les clés composites, creer() de BaseModel peut retourner true/false ou l'array des clés.
        // S'il retourne true, c'est suffisant.
        return $this->creer($data);
    }
}