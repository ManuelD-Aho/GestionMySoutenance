<?php

namespace Backend\Model;

use Backend\Model\BaseModel;

class Approuver extends BaseModel {

    protected string $table = 'approuver';
    protected string $primaryKey = 'id_personnel_administratif'; // First part of composite key

    // Constructor and basic CRUD methods are inherited from BaseModel.
    // Custom methods for composite key operations might be needed.
}
