<?php

<<<<<<< HEAD
/**
 * StatutJury
 * Modèle pour la gestion des données de statutjury
 * 
 * @author Votre Nom
 * @version 1.0
 */

class StatutJury {
    
    protected $table = 'statutjury';
    protected $primaryKey = 'id';
    
    public function __construct() {
        // Initialisation du modèle
    }
    
    public function find($id) {
        // Trouver un enregistrement par ID
    }
    
    public function findAll() {
        // Récupérer tous les enregistrements
    }
    
    public function save($data) {
        // Sauvegarder les données
    }
    
    public function delete($id) {
        // Supprimer un enregistrement
    }
=======
namespace Backend\Model;

use Backend\Model\BaseModel;

class StatutJury extends BaseModel {

    protected string $table = 'statut_jury';
    protected string $primaryKey = 'id_statut_jury';

    // Constructor and basic CRUD methods are inherited from BaseModel.
>>>>>>> origin/refactor-core-and-features-phase1
}
