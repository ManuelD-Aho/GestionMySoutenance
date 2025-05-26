<?php

<<<<<<< HEAD
/**
 * NiveauApprobation
 * Modèle pour la gestion des données de niveauapprobation
 * 
 * @author Votre Nom
 * @version 1.0
 */

class NiveauApprobation {
    
    protected $table = 'niveauapprobation';
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

class NiveauApprobation extends BaseModel {

    protected string $table = 'niveau_approbation';
    protected string $primaryKey = 'id_niveau_approbation';

    // Constructor and basic CRUD methods are inherited from BaseModel.
>>>>>>> origin/refactor-core-and-features-phase1
}
