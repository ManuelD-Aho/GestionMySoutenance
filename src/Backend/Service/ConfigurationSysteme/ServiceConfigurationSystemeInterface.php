<?php
namespace App\Backend\Service\ConfigurationSysteme;

interface ServiceConfigurationSystemeInterface
{
    public function definirAnneeAcademiqueActive(string $idAnneeAcademique): bool;
    public function mettreAJourParametresGeneraux(array $parametres): bool;
    public function recupererParametresGeneraux(): array;
    public function listerAnneesAcademiques(): array;
    public function listerTypesDocument(): array;
    public function listerNiveauxEtude(): array;
    public function listerStatutsPaiement(): array;
    public function listerDecisionsPassage(): array;
    public function listerEcues(): array;
    public function listerGrades(): array;
    public function listerFonctions(): array;
    public function listerSpecialites(): array;
    public function listerStatutsReclamation(): array;
    public function listerStatutsConformite(): array;
}