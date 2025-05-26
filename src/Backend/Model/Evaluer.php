<?php

<<<<<<< HEAD
/**
 * Evaluer
 * Modèle pour la gestion des données de evaluer
 * 
 * @author Votre Nom
 * @version 1.0
 */

class Evaluer {
    
    protected $table = 'evaluer';
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

class Evaluer extends BaseModel {

    protected string $table = 'evaluer';
    protected string $primaryKey = 'id_etudiant'; // First part of composite key

    // Constructor and basic CRUD methods are inherited from BaseModel.
    // Custom methods for composite key operations might be needed.
>>>>>>> origin/refactor-core-and-features-phase1
}
