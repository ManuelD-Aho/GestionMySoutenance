<?php

namespace App\Backend\Service;

use App\Backend\Model\AnneeAcademique;
use App\Backend\Model\TypeDocumentRef;
use App\Backend\Model\Message as ModeleMessageTemplate;
use PDO;

class ServiceConfigurationSysteme
{
    private AnneeAcademique $modeleAnneeAcademique;
    private TypeDocumentRef $modeleTypeDocumentRef;
    private ModeleMessageTemplate $modeleMessageTemplate;
    private PDO $db;

    public function __construct(
        AnneeAcademique $modeleAnneeAcademique,
        TypeDocumentRef $modeleTypeDocumentRef,
        ModeleMessageTemplate $modeleMessageTemplate,
        PDO $db
    ) {
        $this->modeleAnneeAcademique = $modeleAnneeAcademique;
        $this->modeleTypeDocumentRef = $modeleTypeDocumentRef;
        $this->modeleMessageTemplate = $modeleMessageTemplate;
        $this->db = $db;
    }

    public function definirAnneeAcademiqueActive(int $idAnneeAcademique): bool
    {
        $this->db->beginTransaction();
        try {
            $this->modeleAnneeAcademique->executerRequete("UPDATE annee_academique SET est_active = 0 WHERE est_active = 1");
            $succes = $this->modeleAnneeAcademique->mettreAJourParIdentifiant($idAnneeAcademique, ['est_active' => 1]);
            if ($succes) {
                $this->db->commit();
                return true;
            }
            $this->db->rollBack();
            return false;
        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function mettreAJourParametresGeneraux(array $parametres): bool
    {
        $succesGlobal = true;
        $this->db->beginTransaction();
        try {
            foreach ($parametres as $nomParametre => $valeurParametre) {
                // Ceci est un exemple. Une table 'parametres_systeme' (nom_parametre, valeur_parametre) serait nécessaire.
                // $sql = "INSERT INTO parametres_systeme (nom_parametre, valeur_parametre) VALUES (:nom, :valeur) ON DUPLICATE KEY UPDATE valeur_parametre = :valeur";
                // $declaration = $this->db->prepare($sql);
                // if (!$declaration->execute([':nom' => $nomParametre, ':valeur' => $valeurParametre])) {
                //     $succesGlobal = false; break;
                // }
            }
            if ($succesGlobal) {
                $this->db->commit();
            } else {
                $this->db->rollBack();
            }
        } catch (\Exception $e) {
            $this->db->rollBack();
            $succesGlobal = false;
        }
        return $succesGlobal;
    }

    public function gererModeleNotificationEmail(int $idMessage, array $donnees): bool
    {
        if (isset($donnees['id_message'])) unset($donnees['id_message']);
        return $this->modeleMessageTemplate->mettreAJourParIdentifiant($idMessage, $donnees);
    }

    public function listerAnneesAcademiques(): array
    {
        return $this->modeleAnneeAcademique->trouverTout();
    }

    public function listerTypesDocument(): array
    {
        return $this->modeleTypeDocumentRef->trouverTout();
    }
}