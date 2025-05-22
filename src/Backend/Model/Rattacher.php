<?php

namespace Backend\Model;

use Backend\Model\BaseModel;

class Rattacher extends BaseModel {

    protected string $table = 'rattacher';
    protected string $primaryKey = 'id_groupe_utilisateur'; // First part of composite key

    // Constructor and basic CRUD methods are inherited from BaseModel.
    // Custom methods for composite key operations might be needed.
}
