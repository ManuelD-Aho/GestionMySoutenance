<?php

<<<<<<< HEAD
/**
 * Rattacher
 * Modèle pour la gestion des données de rattacher
 * 
 * @author Votre Nom
 * @version 1.0
 */

class Rattacher {
    
    protected $table = 'rattacher';
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

class Rattacher extends BaseModel {

    protected string $table = 'rattacher';
    protected string $primaryKey = 'id_groupe_utilisateur'; // First part of composite key

    // Constructor and basic CRUD methods are inherited from BaseModel.
    // Custom methods for composite key operations might be needed.
>>>>>>> origin/refactor-core-and-features-phase1
}
