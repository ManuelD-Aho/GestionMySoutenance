<?php

<<<<<<< HEAD
/**
 * Pister
 * Modèle pour la gestion des données de pister
 * 
 * @author Votre Nom
 * @version 1.0
 */

class Pister {
    
    protected $table = 'pister';
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

class Pister extends BaseModel {

    protected string $table = 'pister';
    protected string $primaryKey = 'id_utilisateur'; // First part of composite key

    // Constructor and basic CRUD methods are inherited from BaseModel.
    // Custom methods for composite key operations might be needed.
>>>>>>> origin/refactor-core-and-features-phase1
}
