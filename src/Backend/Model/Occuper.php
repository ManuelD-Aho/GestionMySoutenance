<?php

<<<<<<< HEAD
/**
 * Occuper
 * Modèle pour la gestion des données de occuper
 * 
 * @author Votre Nom
 * @version 1.0
 */

class Occuper {
    
    protected $table = 'occuper';
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

class Occuper extends BaseModel {

    protected string $table = 'occuper';
    protected string $primaryKey = 'id_fonction'; // First part of composite key

    // Constructor and basic CRUD methods are inherited from BaseModel.
    // Custom methods for composite key operations might be needed.
>>>>>>> origin/refactor-core-and-features-phase1
}
