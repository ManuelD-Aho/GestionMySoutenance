<?php

/**
 * Fonction
 * Modèle pour la gestion des données de fonction
 * 
 * @author Votre Nom
 * @version 1.0
 */

class Fonction {
    
    protected $table = 'fonction';
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
