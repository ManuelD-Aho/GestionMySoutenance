<?php

namespace App\Backend\Service\GestionAcademique;

use App\Backend\Model\Acquerir;
use App\Backend\Model\Attribuer;
use App\Backend\Model\Evaluer;
use App\Backend\Model\FaireStage;
use App\Backend\Model\Inscrire;
use App\Backend\Model\Occuper;
use PDO; // Ajouté au cas où des méthodes futures en auraient besoin, bien que pas utilisé dans le constructeur actuel

// Assurez-vous que le chemin vers l'interface est correct et que le fichier existe.
// Si l'interface est dans le même namespace, cette ligne 'use' n'est pas strictement
// nécessaire mais peut améliorer la lisibilité.
use App\Backend\Service\GestionAcademique\ServiceGestionAcademiqueInterface;

class ServiceGestionAcademique implements ServiceGestionAcademiqueInterface // <-- AJOUTÉ: implements ServiceGestionAcademiqueInterface
{
    private Inscrire $modeleInscrire;
    private Evaluer $modeleEvaluer;
    private FaireStage $modeleFaireStage;
    private Acquerir $modeleAcquerir;
    private Occuper $modeleOccuper;
    private Attribuer $modeleAttribuer;
    private ?PDO $db; // Ajouté pour la cohérence si des méthodes futures l'utilisent directement

    public function __construct(
        Inscrire $modeleInscrire,
        Evaluer $modeleEvaluer,
        FaireStage $modeleFaireStage,
        Acquerir $modeleAcquerir,
        Occuper $modeleOccuper,
        Attribuer $modeleAttribuer,
        ?PDO $db = null // Ajouté pour permettre l'injection de la BDD si nécessaire, optionnel pour l'instant
    ) {
        $this->modeleInscrire = $modeleInscrire;
        $this->modeleEvaluer = $modeleEvaluer;
        $this->modeleFaireStage = $modeleFaireStage;
        $this->modeleAcquerir = $modeleAcquerir;
        $this->modeleOccuper = $modeleOccuper;
        $this->modeleAttribuer = $modeleAttribuer;
        $this->db = $db; // Stocker la connexion BDD
    }

    // Implémentation des méthodes de l'interface (assurez-vous qu'elles correspondent)
    // Voici les méthodes que vous aviez déjà, vérifiez leur compatibilité avec l'interface.

    public function creerInscriptionAdministrative(string $numeroCarteEtudiant, int $idNiveauEtude, int $idAnneeAcademique, float $montantInscription, string $dateInscription, int $idStatutPaiement, ?string $datePaiement, ?string $numeroRecuPaiement, ?int $idDecisionPassage): ?array
    {
        $donnees = [
            'numero_carte_etudiant' => $numeroCarteEtudiant,
            'id_niveau_etude' => $idNiveauEtude,
            'id_annee_academique' => $idAnneeAcademique,
            'montant_inscription' => $montantInscription,
            'date_inscription' => $dateInscription,
            'id_statut_paiement' => $idStatutPaiement,
            'date_paiement' => $datePaiement,
            'numero_recu_paiement' => $numeroRecuPaiement,
            'id_decision_passage' => $idDecisionPassage
        ];
        // Supposons que la méthode 'creer' du modèle retourne l'ID ou un booléen.
        // Si elle retourne l'ID, et que l'interface attend un array, il faut ajuster.
        // Pour l'instant, on garde votre logique originale.
        $resultat = $this->modeleInscrire->creer($donnees); // La méthode creer de BaseModel retourne l'ID ou false
        if ($resultat !== false) { // Si la création a réussi (retourne un ID)
            // Retourner les données initiales peut être redondant si l'ID est la seule chose qui change.
            // Si l'interface attend l'objet créé ou un array avec son ID :
            // $donnees['id_inscription'] = $resultat; // Si $resultat est l'ID
            return $donnees; // Ou juste $resultat si l'interface attend l'ID
        }
        return null;
    }

    public function mettreAJourInscriptionAdministrative(string $numeroCarteEtudiant, int $idNiveauEtude, int $idAnneeAcademique, array $donneesAMettreAJour): bool
    {
        // La méthode mettreAJourInscriptionParCles n'est pas définie dans BaseModel.
        // Vous devez implémenter cette logique dans Inscrire.php ou utiliser une méthode existante.
        // Exemple si Inscrire étend BaseModel et que BaseModel a une méthode update générique :
        // return $this->modeleInscrire->updateByCompositeKey(
        //    ['numero_carte_etudiant' => $numeroCarteEtudiant, 'id_niveau_etude' => $idNiveauEtude, 'id_annee_academique' => $idAnneeAcademique],
        //    $donneesAMettreAJour
        // );
        // Pour l'instant, je laisse votre appel original, mais il faudra le vérifier.
        if (method_exists($this->modeleInscrire, 'mettreAJourInscriptionParCles')) {
            return $this->modeleInscrire->mettreAJourInscriptionParCles($numeroCarteEtudiant, $idNiveauEtude, $idAnneeAcademique, $donneesAMettreAJour);
        }
        // Fallback ou lever une exception si la méthode n'existe pas
        // throw new \LogicException("La méthode mettreAJourInscriptionParCles n'est pas définie dans le modèle Inscrire.");
        return false; // Placeholder
    }

    public function enregistrerNoteEcue(string $numeroCarteEtudiant, string $numeroEnseignantEvaluateur, int $idEcue, float $note, string $dateEvaluation): bool
    {
        $donnees = [
            'numero_carte_etudiant' => $numeroCarteEtudiant,
            'numero_enseignant' => $numeroEnseignantEvaluateur, // Assurez-vous que la table 'evaluer' a bien 'numero_enseignant'
            'id_ecue' => $idEcue,
            'note' => $note,
            'date_evaluation' => $dateEvaluation
        ];
        return (bool)$this->modeleEvaluer->creer($donnees); // creer retourne l'ID ou false
    }

    public function enregistrerInformationsStage(string $numeroCarteEtudiant, int $idEntreprise, string $dateDebutStage, ?string $dateFinStage, ?string $sujetStage, ?string $nomTuteurEntreprise): bool
    {
        $donnees = [
            'numero_carte_etudiant' => $numeroCarteEtudiant,
            'id_entreprise' => $idEntreprise,
            'date_debut_stage' => $dateDebutStage,
            'date_fin_stage' => $dateFinStage,
            'sujet_stage' => $sujetStage,
            'nom_tuteur_entreprise' => $nomTuteurEntreprise
        ];
        return (bool)$this->modeleFaireStage->creer($donnees);
    }

    public function lierGradeAEnseignant(string $numeroEnseignant, int $idGrade, string $dateAcquisition): bool
    {
        $donnees = [
            'numero_enseignant' => $numeroEnseignant,
            'id_grade' => $idGrade,
            'date_acquisition' => $dateAcquisition
        ];
        return (bool)$this->modeleAcquerir->creer($donnees);
    }

    public function lierFonctionAEnseignant(string $numeroEnseignant, int $idFonction, string $dateDebutOccupation, ?string $dateFinOccupation): bool
    {
        $donnees = [
            'numero_enseignant' => $numeroEnseignant,
            'id_fonction' => $idFonction,
            'date_debut_occupation' => $dateDebutOccupation,
            'date_fin_occupation' => $dateFinOccupation
        ];
        return (bool)$this->modeleOccuper->creer($donnees);
    }

    public function lierSpecialiteAEnseignant(string $numeroEnseignant, int $idSpecialite): bool
    {
        $donnees = [
            'numero_enseignant' => $numeroEnseignant,
            'id_specialite' => $idSpecialite
        ];
        return (bool)$this->modeleAttribuer->creer($donnees);
    }

    // Ajoutez ici d'autres méthodes si elles sont définies dans ServiceGestionAcademiqueInterface
    // et ne sont pas encore présentes dans cette classe.
}
