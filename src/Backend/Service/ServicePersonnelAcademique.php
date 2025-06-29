<?php

declare(strict_types=1);

namespace App\Backend\Service;

use App\Backend\Model\Enseignant;
use App\Backend\Model\PersonnelAdministratif;
use App\Backend\Model\Attribuer;
use App\Backend\Model\Occuper;
use App\Backend\Model\Acquerir;
use App\Backend\Service\Interface\PersonnelAcademiqueServiceInterface;
use App\Backend\Exception\ElementNonTrouveException;
use App\Backend\Exception\DoublonException;

class ServicePersonnelAcademique implements PersonnelAcademiqueServiceInterface
{
    private Enseignant $enseignantModel;
    private PersonnelAdministratif $personnelAdminModel;
    private Attribuer $attribuerModel;
    private Occuper $occuperModel;
    private Acquerir $acquerirModel;

    public function __construct(
        Enseignant $enseignantModel,
        PersonnelAdministratif $personnelAdminModel,
        Attribuer $attribuerModel,
        Occuper $occuperModel,
        Acquerir $acquerirModel
    ) {
        $this->enseignantModel = $enseignantModel;
        $this->personnelAdminModel = $personnelAdminModel;
        $this->attribuerModel = $attribuerModel;
        $this->occuperModel = $occuperModel;
        $this->acquerirModel = $acquerirModel;
    }

    /**
     * @inheritdoc
     */
    public function getProfilEnseignant(string $numeroEnseignant): array
    {
        $enseignant = $this->enseignantModel->trouverParIdentifiant($numeroEnseignant);
        if (!$enseignant) {
            throw new ElementNonTrouveException("Profil enseignant non trouvé.");
        }
        return $enseignant;
    }

    /**
     * @inheritdoc
     */
    public function assignerSpecialiteAEnseignant(string $numeroEnseignant, string $idSpecialite): bool
    {
        $this->enseignantModel->trouverParIdentifiant($numeroEnseignant) ?: throw new ElementNonTrouveException("Enseignant non trouvé.");
        // Le modèle Specialite devrait être utilisé pour valider $idSpecialite

        if ($this->attribuerModel->compterParCritere(['numero_enseignant' => $numeroEnseignant, 'id_specialite' => $idSpecialite]) > 0) {
            throw new DoublonException("L'enseignant possède déjà cette spécialité.");
        }

        return $this->attribuerModel->creer([
            'numero_enseignant' => $numeroEnseignant,
            'id_specialite' => $idSpecialite
        ]);
    }

    /**
     * @inheritdoc
     */
    public function ajouterGradeHistorique(string $numeroEnseignant, string $idGrade, \DateTimeInterface $dateAcquisition): bool
    {
        return $this->acquerirModel->creer([
            'numero_enseignant' => $numeroEnseignant,
            'id_grade' => $idGrade,
            'date_acquisition' => $dateAcquisition->format('Y-m-d')
        ]);
    }

    /**
     * @inheritdoc
     */
    public function ajouterFonctionHistorique(string $numeroEnseignant, string $idFonction, \DateTimeInterface $dateDebut, ?\DateTimeInterface $dateFin = null): bool
    {
        return $this->occuperModel->creer([
            'numero_enseignant' => $numeroEnseignant,
            'id_fonction' => $idFonction,
            'date_debut_occupation' => $dateDebut->format('Y-m-d'),
            'date_fin_occupation' => $dateFin ? $dateFin->format('Y-m-d') : null
        ]);
    }

    /**
     * @inheritdoc
     */
    public function listerPersonnel(string $typePersonnel, array $criteres): array
    {
        if ($typePersonnel === 'enseignant') {
            return $this->enseignantModel->trouverParCritere($criteres);
        } elseif ($typePersonnel === 'administratif') {
            return $this->personnelAdminModel->trouverParCritere($criteres);
        }
        throw new \InvalidArgumentException("Type de personnel non valide.");
    }
}