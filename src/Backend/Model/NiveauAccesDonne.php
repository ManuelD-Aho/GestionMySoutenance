<?php

<<<<<<< HEAD
/**
 * NiveauAccesDonne
 * Modèle pour la gestion des données de niveauaccesdonne
 * 
 * @author Votre Nom
 * @version 1.0
 */

class NiveauAccesDonne {
    
    protected $table = 'niveauaccesdonne';
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

class NiveauAccesDonne extends BaseModel {

    protected string $table = 'niveau_acces_donne';
    protected string $primaryKey = 'id_niveau_acces_donne';

    // Constructor and basic CRUD methods are inherited from BaseModel.
>>>>>>> origin/refactor-core-and-features-phase1
}
