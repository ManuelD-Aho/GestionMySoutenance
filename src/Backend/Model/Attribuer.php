<?php

namespace Backend\Model;

use Backend\Model\BaseModel;

class Attribuer extends BaseModel {

    protected string $table = 'attribuer';
    protected string $primaryKey = 'id_enseignant'; // First part of composite key

    // Constructor and basic CRUD methods are inherited from BaseModel.
    // Custom methods for composite key operations might be needed.
}
