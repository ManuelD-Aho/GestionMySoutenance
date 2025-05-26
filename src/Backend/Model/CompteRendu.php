<?php

<<<<<<< HEAD
/**
 * CompteRendu
 * Modèle pour la gestion des données de compterendu
 * 
 * @author Votre Nom
 * @version 1.0
 */

class CompteRendu {
    
    protected $table = 'compterendu';
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

class CompteRendu extends BaseModel {

    protected string $table = 'compte_rendu';
    protected string $primaryKey = 'id_compte_rendu';

    // Constructor and basic CRUD methods are inherited from BaseModel.
>>>>>>> origin/refactor-core-and-features-phase1
}
