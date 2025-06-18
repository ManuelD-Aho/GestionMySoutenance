<?php

namespace App\Config;

use PDO;
use App\Backend\Service\Authentication\ServiceAuthenticationInterface;
use App\Backend\Service\Authentication\ServiceAuthentification;
use App\Backend\Model\Utilisateur;
use App\Backend\Model\HistoriqueMotDePasse;
use App\Backend\Model\Etudiant;
use App\Backend\Model\Enseignant;
use App\Backend\Model\PersonnelAdministratif;
use App\Backend\Service\Email\ServiceEmail;
use App\Backend\Service\SupervisionAdmin\ServiceSupervisionAdmin;
use App\Backend\Service\GestionAcademique\ServiceGestionAcademique;
use App\Backend\Service\Permissions\ServicePermissions;
use RobThree\Auth\TwoFactorAuth;
use RobThree\Auth\Providers\Qr\BaconQrCodeProvider;

class Container
{
    private array $instances = [];

    public function getDb(): PDO
    {
        if (!isset($this->instances['db'])) {
            $this->instances['db'] = Database::getInstance()->getConnection();
        }
        return $this->instances['db'];
    }

    public function getAuthService(): ServiceAuthenticationInterface
    {
        if (!isset($this->instances['auth_service'])) {
            $tfaProvider = new TwoFactorAuth($_ENV['APP_NAME'] ?? 'GestionMySoutenance');

            $this->instances['auth_service'] = new ServiceAuthentification(
                $this->getDb(),
                new ServiceEmail(),
                $this->getSupervisionService(),
                $this->getGestionAcademiqueService(),
                $this->getPermissionsService(),
                $tfaProvider,
                new Utilisateur($this->getDb()),
                new HistoriqueMotDePasse($this->getDb()),
                new Etudiant($this->getDb()),
                new Enseignant($this->getDb()),
                new PersonnelAdministratif($this->getDb())
            );
        }
        return $this->instances['auth_service'];
    }

    public function getSupervisionService(): ServiceSupervisionAdmin
    {
        if (!isset($this->instances['supervision_service'])) {
            $this->instances['supervision_service'] = new ServiceSupervisionAdmin(
                new \App\Backend\Model\RapportEtudiant($this->getDb()),
                new \App\Backend\Model\Enregistrer($this->getDb()),
                new \App\Backend\Model\Pister($this->getDb()),
                new \App\Backend\Model\CompteRendu($this->getDb()),
                $this->getDb()
            );
        }
        return $this->instances['supervision_service'];
    }

    public function getGestionAcademiqueService(): ServiceGestionAcademique
    {
        if (!isset($this->instances['gestion_academique_service'])) {
            $this->instances['gestion_academique_service'] = new ServiceGestionAcademique(
                new \App\Backend\Model\Inscrire($this->getDb()),
                new \App\Backend\Model\Evaluer($this->getDb()),
                new \App\Backend\Model\FaireStage($this->getDb()),
                new \App\Backend\Model\Acquerir($this->getDb()),
                new \App\Backend\Model\Occuper($this->getDb()),
                new \App\Backend\Model\Attribuer($this->getDb()),
                $this->getDb()
            );
        }
        return $this->instances['gestion_academique_service'];
    }

    public function getPermissionsService(): ServicePermissions
    {
        if (!isset($this->instances['permissions_service'])) {
            $this->instances['permissions_service'] = new ServicePermissions(
                $this->getDb(),
                $this->getSupervisionService()
            );
        }
        return $this->instances['permissions_service'];
    }
}