<?php

namespace App\Backend\Service;

use App\Backend\Model\Utilisateur;
use App\Backend\Model\Rattacher;
use App\Backend\Model\Traitement;
use App\Backend\Model\GroupeUtilisateur;

class ServicePermissions
{
    private Utilisateur $modeleUtilisateur;
    private Rattacher $modeleRattacher;
    private Traitement $modeleTraitement;
    private GroupeUtilisateur $modeleGroupeUtilisateur;

    public function __construct(
        Utilisateur $modeleUtilisateur,
        Rattacher $modeleRattacher,
        Traitement $modeleTraitement,
        GroupeUtilisateur $modeleGroupeUtilisateur
    ) {
        $this->modeleUtilisateur = $modeleUtilisateur;
        $this->modeleRattacher = $modeleRattacher;
        $this->modeleTraitement = $modeleTraitement;
        $this->modeleGroupeUtilisateur = $modeleGroupeUtilisateur;
    }

    public function verifierAccesTraitement(string $numeroUtilisateurCourant, int $identifiantTraitementRequis): bool
    {
        $utilisateur = $this->modeleUtilisateur->trouverParIdentifiant($numeroUtilisateurCourant, ['id_groupe_utilisateur']);
        if (!$utilisateur || !isset($utilisateur['id_groupe_utilisateur'])) {
            return false;
        }
        $idGroupeUtilisateur = $utilisateur['id_groupe_utilisateur'];
        $rattachement = $this->modeleRattacher->trouverRattachementParCles($idGroupeUtilisateur, $identifiantTraitementRequis);
        return $rattachement !== null;
    }

    public function recupererElementsMenuAutorises(string $numeroUtilisateurCourant): array
    {
        $utilisateur = $this->modeleUtilisateur->trouverParIdentifiant($numeroUtilisateurCourant, ['id_groupe_utilisateur']);
        if (!$utilisateur || !isset($utilisateur['id_groupe_utilisateur'])) {
            return [];
        }
        $idGroupeUtilisateur = $utilisateur['id_groupe_utilisateur'];
        $rattachements = $this->modeleRattacher->trouverParCritere(['id_groupe_utilisateur' => $idGroupeUtilisateur], ['id_traitement']);

        $elementsMenu = [];
        $idsTraitementsAutorises = array_column($rattachements, 'id_traitement');

        if (empty($idsTraitementsAutorises)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($idsTraitementsAutorises), '?'));
        $sql = "SELECT id_traitement, lib_trait, module_associe, url_menu, icone_menu FROM traitement WHERE id_traitement IN ({$placeholders}) ORDER BY ordre_menu ASC";

        $declaration = $this->modeleTraitement->executerRequete($sql, $idsTraitementsAutorises);
        $traitementsDetails = $declaration->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($traitementsDetails as $detail) {
            $elementsMenu[] = [
                'libelle' => $detail['lib_trait'],
                'url' => $detail['url_menu'] ?? '#',
                'icone' => $detail['icone_menu'] ?? 'fas fa-circle',
                'module' => $detail['module_associe'] ?? 'General',
                'id_traitement' => $detail['id_traitement']
            ];
        }
        return $elementsMenu;
    }
}