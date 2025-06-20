<?php
namespace App\Backend\Model;

use PDO;

class Sequences extends BaseModel
{
    protected string $table = 'sequences';
    // La clé primaire est composite: nom_sequence (VARCHAR(50)) et annee (YEAR)
    protected string|array $primaryKey = ['nom_sequence', 'annee'];

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }

    /**
     * Trouve une séquence spécifique par son nom et son année.
     * @param string $nomSequence Le nom logique du compteur (ex: 'rapport', 'etudiant').
     * @param int $annee L'année concernée par le compteur.
     * @param array $colonnes Les colonnes à sélectionner.
     * @return array|null Les données de la séquence ou null si non trouvée.
     */
    public function trouverSequence(string $nomSequence, int $annee, array $colonnes = ['*']): ?array
    {
        return $this->trouverUnParCritere([
            'nom_sequence' => $nomSequence,
            'annee' => $annee
        ], $colonnes);
    }
}