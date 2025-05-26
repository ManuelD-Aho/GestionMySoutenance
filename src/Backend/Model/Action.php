<?php

/**
 * Action
 * Modèle pour la gestion des données de action
 * 
 * @author Votre Nom
 * @version 1.0
 */

class Action {
    
    protected $table = 'action';
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
}
