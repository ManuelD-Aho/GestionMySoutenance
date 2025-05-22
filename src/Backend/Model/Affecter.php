<?php

namespace Backend\Model;

use Backend\Model\BaseModel;

class Affecter extends BaseModel {

    protected string $table = 'affecter';
    protected string $primaryKey = 'id_enseignant'; // First part of composite key

    // Constructor and basic CRUD methods are inherited from BaseModel.
    // Custom methods for composite key operations might be needed if BaseModel's
    // findBy, query, etc., are not sufficient for all use cases.
}
