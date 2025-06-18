<?php

/**
 * UtilisateurController
 * Contrôleur pour la gestion de utilisateurcontroller
 * 
 * @author Votre Nom
 * @version 1.0
 */
namespace App\Backend\Controller\Administration;

use App\Backend\Controller\BaseController;
use App\Backend\Service\Authentication\ServiceAuthenticationInterface;

class UtilisateurController {
    
    public function __construct() {
        parent::__construct();
        // On récupère le service d'authentification, qui contient la méthode pour lister les utilisateurs
        $this->authService = $this->container->getAuthService();
    }
    
    public function index() {
        // Action par défaut
    }
    
    public function create() {
        // Créer un nouvel élément
    }
    
    public function update($id) {
        // Mettre à jour un élément
    }
    
    public function delete($id) {
        // Supprimer un élément
    }
    /**
     * Affiche la liste de tous les utilisateurs avec pagination.
     */
    public function listerUtilisateurs(): void
    {
        // 1. Sécurité : Vérifier que l'utilisateur a le droit de voir cette page.
        // Nous utiliserons un nom de permission conforme à la documentation.
        $this->requirePermission('TRAIT_USER_VIEW_ALL'); //

        // 2. Logique : Récupérer les données depuis le service
        // La méthode `listerUtilisateursAvecProfils` existe déjà dans votre ServiceAuthentification
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $resultat = $this->authService->listerUtilisateursAvecProfils([], $page, 20);

        $utilisateurs = $resultat['utilisateurs'];
        $totalElements = $resultat['total_elements'];
        $totalPages = ceil($totalElements / 20);

        // 3. Affichage : Envoyer les données à la vue
        $this->render('Administration/Utilisateurs/liste_utilisateurs', [
            'title' => 'Gestion des Utilisateurs',
            'utilisateurs' => $utilisateurs,
            'page_actuelle' => $page,
            'total_pages' => $totalPages
        ]);
    }
}
