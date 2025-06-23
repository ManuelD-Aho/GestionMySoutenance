<?php
namespace App\Backend\Service\DocumentGenerator;

interface ServiceDocumentGeneratorInterface
{
    public function genererPvValidation(string $idCompteRendu): string;
    public function genererAttestationScolarite(string $numeroEtudiant): string;
    public function genererBulletinNotes(string $numeroEtudiant, string $idAnneeAcademique): string;
}