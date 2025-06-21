<?php

namespace App\Backend\Util;

/**
 * FormValidator
 * Utilitaire pour la validation des formulaires
 */

class FormValidator {
    
    public static function validate($data, $rules) {
        // Logique de validation
        return true;
    }
    
    public static function sanitize($input) {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
}
