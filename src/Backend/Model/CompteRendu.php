<?php

namespace Backend\Model;

use Backend\Model\BaseModel;

class CompteRendu extends BaseModel {

    protected string $table = 'compte_rendu';
    protected string $primaryKey = 'id_compte_rendu';

    // Constructor and basic CRUD methods are inherited from BaseModel.
}
