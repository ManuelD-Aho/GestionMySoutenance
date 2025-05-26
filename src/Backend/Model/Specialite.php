<?php

/**
 * Specialite
 * Modèle pour la gestion des données de specialite
 * 
 * @author Votre Nom
 * @version 1.0
 */

class Specialite {
    
    protected $table = 'specialite';
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
