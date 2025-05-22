<?php

namespace Backend\Model;

use Backend\Model\BaseModel;

class Grade extends BaseModel {

    protected string $table = 'grade';
    protected string $primaryKey = 'id_grade';

    // Constructor and basic CRUD methods are inherited from BaseModel.
}
