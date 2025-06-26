<?php

namespace App\Backend\Service\ProfilEtudiant;

use PDO;
use App\Backend\Model\Etudiant;
use App\Backend\Model\Utilisateur;
use App\Backend\Service\SupervisionAdmin\ServiceSupervisionAdminInterface;
use App\Backend\Service\Fichier\ServiceFichierInterface;
use App\Backend\Exception\ElementNonTrouveException;
use App\Backend\Exception\OperationImpossibleException;

class ServiceProfilEtudiant implements ServiceProfilEtudiantInterface
{
    private Etudiant $etudiantModel;
    private Utilisateur $utilisateurModel;
    private ServiceSupervisionAdminInterface $supervisionService;
    private ServiceFichierInterface $fichierService;

    public function __construct(
        PDO $db,
        Etudiant $etudiantModel,
        Utilisateur $utilisateurModel,
        ServiceSupervisionAdminInterface $supervisionService,
        ServiceFichierInterface $fichierService
    ) {
        $this->etudiantModel = $etudiantModel;
        $this->utilisateurModel = $utilisateurModel;
        $this->supervisionService = $supervisionService;
        $this->fichierService = $fichierService;
    }

    public function mettreAJourCoordonneesPersonnelles(string $numeroEtudiant, array $donnees): bool
    {
        if (!$this->etudiantModel->trouverParIdentifiant($numeroEtudiant)) {
            throw new ElementNonTrouveException("Profil étudiant non trouvé.");
        }

        $success = $this->etudiantModel->mettreAJourParIdentifiant($numeroEtudiant, $donnees);
        if ($success) {
            $this->supervisionService->enregistrerAction(
                $numeroEtudiant,
                'MAJ_PROFIL_ETUDIANT',
                "Coordonnées personnelles mises à jour."
            );
        }
        return $success;
    }

    public function telechargerPhotoProfil(string $numeroEtudiant, array $fileData): string
    {
        $utilisateur = $this->utilisateurModel->trouverParIdentifiant($numeroEtudiant);
        if (!$utilisateur) {
            throw new ElementNonTrouveException("Utilisateur non trouvé.");
        }

        $destinationPath = 'profile_pictures';
        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $maxSize = 2 * 1024 * 1024; // 2 MB

        $nouveauChemin = $this->fichierService->uploadFichier($fileData, $destinationPath, $allowedMimeTypes, $maxSize);

        if ($utilisateur['photo_profil'] && $utilisateur['photo_profil'] !== $nouveauChemin) {
            $this->fichierService->supprimerFichier($utilisateur['photo_profil']);
        }

        $this->utilisateurModel->mettreAJourParIdentifiant($numeroEtudiant, ['photo_profil' => $nouveauChemin]);
        $this->supervisionService->enregistrerAction(
            $numeroEtudiant,
            'UPLOAD_PHOTO_PROFIL',
            "Photo de profil mise à jour."
        );

        return $nouveauChemin;
    }

    public function getProfilEtudiant(string $numeroEtudiant): ?array
    {
        return $this->etudiantModel->trouverParIdentifiant($numeroEtudiant);
    }
}