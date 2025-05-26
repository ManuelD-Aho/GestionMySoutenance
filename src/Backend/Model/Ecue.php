<?php

<<<<<<< HEAD
/**
 * Ecue
 * Modèle pour la gestion des données de ecue
 * 
 * @author Votre Nom
 * @version 1.0
 */

class Ecue {
    
    protected $table = 'ecue';
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

class Ecue extends BaseModel {

    protected string $table = 'ecue';
    protected string $primaryKey = 'id_ecue';

    // Constructor and basic CRUD methods are inherited from BaseModel.
>>>>>>> origin/refactor-core-and-features-phase1
}
