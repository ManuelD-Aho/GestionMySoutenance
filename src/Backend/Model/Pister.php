<?php

namespace Backend\Model;

use Backend\Model\BaseModel;

class Pister extends BaseModel {

    protected string $table = 'pister';
    protected string $primaryKey = 'id_utilisateur'; // First part of composite key

    // Constructor and basic CRUD methods are inherited from BaseModel.
    // Custom methods for composite key operations might be needed.
}
