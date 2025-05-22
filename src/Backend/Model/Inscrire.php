<?php

namespace Backend\Model;

use Backend\Model\BaseModel;

class Inscrire extends BaseModel {

    protected string $table = 'inscrire';
    protected string $primaryKey = 'id_etudiant'; // First part of composite key

    // Constructor and basic CRUD methods are inherited from BaseModel.
    // Custom methods for composite key operations might be needed.
}
