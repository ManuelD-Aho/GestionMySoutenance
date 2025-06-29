<?php

declare(strict_types=1);

namespace App\Backend\Service;

use PDO;
use App\Backend\Model\Etudiant;
use App\Backend\Service\Interface\ProfilEtudiantServiceInterface;
use App\Backend\Service\Interface\FichierServiceInterface;
use App\Backend\Service\Interface\AuditServiceInterface;
use App\Backend\Service\Interface\IdentifiantGeneratorInterface;
use App\Backend\Service\Interface\InscriptionServiceInterface;
use App\Backend\Service\Interface\NotationServiceInterface;
use App\Backend\Service\Interface\StageServiceInterface;
use App\Backend\Exception\DoublonException;
use App\Backend\Exception\ElementNonTrouveException;

class ServiceProfilEtudiant implements ProfilEtudiantServiceInterface
{
    private PDO $pdo;
    private Etudiant $etudiantModel;
    private FichierServiceInterface $fichierService;
    private AuditServiceInterface $auditService;
    private IdentifiantGeneratorInterface $identifiantGenerator;
    private InscriptionServiceInterface $inscriptionService;
    private NotationServiceInterface $notationService;
    private StageServiceInterface $stageService;

    public function __construct(
        PDO $pdo,
        Etudiant $etudiantModel,
        FichierServiceInterface $fichierService,
        AuditServiceInterface $auditService,
        IdentifiantGeneratorInterface $identifiantGenerator,
        InscriptionServiceInterface $inscriptionService,
        NotationServiceInterface $notationService,
        StageServiceInterface $stageService
    ) {
        $this->pdo = $pdo;
        $this->etudiantModel = $etudiantModel;
        $this->fichierService = $fichierService;
        $this->auditService = $auditService;
        $this->identifiantGenerator = $identifiantGenerator;
        $this->inscriptionService = $inscriptionService;
        $this->notationService = $notationService;
        $this->stageService = $stageService;
    }

    public function creerEtudiant(array $donnees): string
    {
        if ($this->etudiantModel->trouverUnParCritere(['email_etudiant' => $donnees['email_etudiant']])) {
            throw new DoublonException("Un étudiant avec l'email '{$donnees['email_etudiant']}' existe déjà.");
        }

        $numeroCarte = $this->identifiantGenerator->generer('ETU');
        $donnees['numero_carte_etudiant'] = $numeroCarte;

        $this->etudiantModel->creer($donnees);
        $this->auditService->enregistrerAction('ManuelD-Aho', 'STUDENT_PROFILE_CREATED', $numeroCarte, 'Etudiant', $donnees);

        return $numeroCarte;
    }

    public function mettreAJourProfil(string $numeroEtudiant, array $donnees): bool
    {
        $etudiant = $this->recupererOuEchouer($numeroEtudiant);
        $resultat = $this->etudiantModel->mettreAJourParIdentifiant($numeroEtudiant, $donnees);
        $this->auditService->enregistrerAction($numeroEtudiant, 'STUDENT_PROFILE_UPDATED', $numeroEtudiant, 'Etudiant', ['anciennes_valeurs' => $etudiant, 'nouvelles_valeurs' => $donnees]);
        return $resultat;
    }

    public function recupererProfil(string $numeroEtudiant): ?array
    {
        return $this->etudiantModel->trouverParIdentifiant($numeroEtudiant);
    }

    public function mettreAJourPhotoProfil(string $numeroEtudiant, array $fichier): string
    {
        $etudiant = $this->recupererOuEchouer($numeroEtudiant);

        if (!empty($etudiant['photo_profil'])) {
            $this->fichierService->supprimer($etudiant['photo_profil']);
        }

        $cheminPhoto = $this->fichierService->uploader($fichier, 'photos_profil');
        $this->etudiantModel->mettreAJourParIdentifiant($numeroEtudiant, ['photo_profil' => $cheminPhoto]);
        $this->auditService->enregistrerAction($numeroEtudiant, 'STUDENT_PHOTO_UPDATED', $numeroEtudiant, 'Etudiant');

        return $cheminPhoto;
    }

    public function supprimerPhotoProfil(string $numeroEtudiant): bool
    {
        $etudiant = $this->recupererOuEchouer($numeroEtudiant);
        if (empty($etudiant['photo_profil'])) {
            return true;
        }

        if ($this->fichierService->supprimer($etudiant['photo_profil'])) {
            $this->etudiantModel->mettreAJourParIdentifiant($numeroEtudiant, ['photo_profil' => null]);
            $this->auditService->enregistrerAction($numeroEtudiant, 'STUDENT_PHOTO_DELETED', $numeroEtudiant, 'Etudiant');
            return true;
        }
        return false;
    }

    public function getDossierComplet(string $numeroEtudiant): array
    {
        $profil = $this->recupererOuEchouer($numeroEtudiant);

        $inscriptions = $this->inscriptionService->listerInscriptions(['numero_carte_etudiant' => $numeroEtudiant]);
        $notes = $this->notationService->listerNotesParEtudiant($numeroEtudiant, null);
        $stages = $this->stageService->listerStages(['numero_carte_etudiant' => $numeroEtudiant]);

        $dossier = [
            'profil' => $profil,
            'inscriptions' => $inscriptions,
            'notes' => $notes,
            'stages' => $stages
        ];

        return $dossier;
    }

    private function recupererOuEchouer(string $numeroEtudiant): array
    {
        $etudiant = $this->etudiantModel->trouverParIdentifiant($numeroEtudiant);
        if (!$etudiant) {
            throw new ElementNonTrouveException("L'étudiant avec le numéro '{$numeroEtudiant}' n'a pas été trouvé.");
        }
        return $etudiant;
    }
}