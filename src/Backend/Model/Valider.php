<?php

<<<<<<< HEAD
/**
 * Valider
 * Modèle pour la gestion des données de valider
 * 
 * @author Votre Nom
 * @version 1.0
 */

class Valider {
    
    protected $table = 'valider';
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

class Valider extends BaseModel {

    protected string $table = 'valider';
    protected string $primaryKey = 'id_enseignant'; // First part of composite key

    // Constructor and basic CRUD methods are inherited from BaseModel.
    // Custom methods for composite key operations might be needed.
>>>>>>> origin/refactor-core-and-features-phase1
}
