<?php

namespace Backend\Model;

use Backend\Model\BaseModel;

class Entreprise extends BaseModel {

    protected string $table = 'entreprise';
    protected string $primaryKey = 'id_entreprise';

    // Constructor and basic CRUD methods are inherited from BaseModel.
}
