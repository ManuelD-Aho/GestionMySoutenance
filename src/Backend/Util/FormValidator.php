<?php

namespace App\Backend\Util;

/**
 * FormValidator
 * Utilitaire pour la validation des données de formulaire selon des règles définies.
 */
class FormValidator
{
    private array $data;   // Données à valider
    private array $errors; // Erreurs de validation
    private array $rules;  // Règles de validation

    public function __construct()
    {
        // Initialisation explicite des propriétés pour éviter les avertissements d'IDE et garantir l'état initial
        $this->data = [];
        $this->errors = [];
        $this->rules = [];
    }

    /**
     * Valide les données fournies selon les règles définies.
     * Cette méthode est le point d'entrée principal pour exécuter la validation.
     *
     * @param array $data Les données à valider (généralement $_POST ou les données nettoyées du contrôleur).
     * @param array $rules Les règles de validation, formatées comme 'champ' => 'regle1|regle2:param|...'.
     */
    public function validate(array $data, array $rules): void
    {
        $this->data = $data;
        $this->rules = $rules;
        $this->errors = []; // Réinitialiser les erreurs pour chaque nouvelle validation

        foreach ($this->rules as $field => $fieldRules) {
            // Récupère la valeur du champ, null si non définie
            $value = $this->data[$field] ?? null;
            // Divise les règles pour le champ (ex: 'required|email|min:5')
            $rulesArray = explode('|', $fieldRules);

            foreach ($rulesArray as $rule) {
                // Sépare le nom de la règle du paramètre (ex: 'min' et '5')
                list($ruleName, $param) = array_pad(explode(':', $rule, 2), 2, null);

                // Exécute la méthode de validation correspondante
                switch ($ruleName) {
                    case 'required':
                        $this->validateRequired($field, $value);
                        break;
                    case 'email':
                        $this->validateEmail($field, $value);
                        break;
                    case 'string':
                        $this->validateString($field, $value);
                        break;
                    case 'min':
                    case 'minlength': // Alias
                        $this->validateMinLength($field, $value, $param);
                        break;
                    case 'max':
                    case 'maxlength': // Alias
                        $this->validateMaxLength($field, $value, $param);
                        break;
                    case 'integer':
                        $this->validateInteger($field, $value);
                        break;
                    case 'numeric':
                        $this->validateNumeric($field, $value);
                        break;
                    case 'date':
                        $this->validateDate($field, $value);
                        break;
                    case 'same':
                        $this->validateSame($field, $value, $param);
                        break;
                    case 'in':
                        $this->validateIn($field, $value, $param);
                        break;
                    case 'boolean':
                        $this->validateBoolean($field, $value);
                        break;
                    case 'required_if':
                        // Format: required_if:other_field,other_value
                        list($otherField, $otherValue) = array_pad(explode(',', $param, 2), 2, null);
                        $this->validateRequiredIf($field, $value, $otherField, $otherValue);
                        break;
                    case 'after_or_equal': // Pour les dates (format YYYY-MM-DD)
                        $this->validateAfterOrEqual($field, $value, $param);
                        break;
                    case 'before_or_equal': // Pour les dates (format YYYY-MM-DD)
                        $this->validateBeforeOrEqual($field, $value, $param);
                        break;
                    case 'length': // Pour une longueur exacte, ex: length:6 pour un code TOTP
                        $this->validateLength($field, $value, $param);
                        break;
                    // Ajouter d'autres règles ici si nécessaire
                    default:
                        // Si une règle n'est pas reconnue, on peut choisir d'ignorer ou de générer une erreur
                        // $this->addError($field, "Règle de validation '{$ruleName}' non reconnue.");
                        break;
                }
                // Si le champ a déjà une erreur 'required' et qu'il est vide,
                // on ne continue pas de valider les autres règles pour ce champ.
                if (isset($this->errors[$field]) && in_array("Le champ '{$field}' est requis.", $this->errors[$field]) && empty($value) && $value !== 0 && $value !== '0') {
                    break;
                }
            }
        }
    }

    /**
     * Ajoute un message d'erreur pour un champ spécifique.
     *
     * @param string $field Le nom du champ.
     * @param string $message Le message d'erreur.
     */
    private function addError(string $field, string $message): void
    {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }
        $this->errors[$field][] = $message;
    }

    /**
     * Récupère toutes les erreurs de validation.
     *
     * @return array Un tableau associatif des erreurs (champ => [messages]).
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Vérifie si la validation a réussi (c'est-à-dire s'il n'y a pas d'erreurs).
     *
     * @return bool Vrai si aucune erreur n'a été trouvée, faux sinon.
     */
    public function isValid(): bool
    {
        return empty($this->errors);
    }

    // --- Méthodes de validation individuelles (private, implémentent la logique de chaque règle) ---

    private function validateRequired(string $field, mixed $value): void
    {
        if (empty($value) && $value !== 0 && $value !== '0') { // 0 et '0' sont considérés comme non vides
            $this->addError($field, "Le champ '{$field}' est requis.");
        }
    }

    private function validateEmail(string $field, ?string $value): void
    {
        if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->addError($field, "Le champ '{$field}' doit être une adresse e-mail valide.");
        }
    }

    private function validateString(string $field, mixed $value): void
    {
        if ($value !== null && !is_string($value)) { // Gère aussi les cas où la valeur est vide mais pas null
            $this->addError($field, "Le champ '{$field}' doit être une chaîne de caractères.");
        }
    }

    private function validateMinLength(string $field, ?string $value, string $param): void
    {
        if (!empty($value) && strlen($value) < (int)$param) {
            $this->addError($field, "Le champ '{$field}' doit contenir au moins {$param} caractères.");
        }
    }

    private function validateMaxLength(string $field, ?string $value, string $param): void
    {
        if (!empty($value) && strlen($value) > (int)$param) {
            $this->addError($field, "Le champ '{$field}' ne doit pas dépasser {$param} caractères.");
        }
    }

    private function validateLength(string $field, ?string $value, string $param): void
    {
        if (!empty($value) && strlen($value) !== (int)$param) {
            $this->addError($field, "Le champ '{$field}' doit contenir exactement {$param} caractères.");
        }
    }

    private function validateInteger(string $field, mixed $value): void
    {
        if ($value !== null && !filter_var($value, FILTER_VALIDATE_INT)) {
            $this->addError($field, "Le champ '{$field}' doit être un entier valide.");
        }
    }

    private function validateNumeric(string $field, mixed $value): void
    {
        if ($value !== null && !is_numeric($value)) {
            $this->addError($field, "Le champ '{$field}' doit être une valeur numérique.");
        }
    }

    private function validateDate(string $field, ?string $value): void
    {
        if (!empty($value)) {
            $d = \DateTime::createFromFormat('Y-m-d', $value);
            // Vérifie le format et la validité de la date (ex: pas 30 février)
            if (!($d && $d->format('Y-m-d') === $value)) {
                $this->addError($field, "Le champ '{$field}' doit être une date valide (YYYY-MM-DD).");
            }
        }
    }

    private function validateSame(string $field, mixed $value, string $param): void
    {
        // Vérifie si le champ de comparaison existe dans les données validées
        if (isset($this->data[$param]) && $value !== $this->data[$param]) {
            $this->addError($field, "Le champ '{$field}' doit correspondre au champ '{$param}'.");
        }
    }

    private function validateIn(string $field, ?string $value, string $param): void
    {
        $choices = array_map('trim', explode(',', $param)); // Nettoyer les choix
        if (!empty($value) && !in_array($value, $choices)) {
            $this->addError($field, "La valeur du champ '{$field}' n'est pas valide.");
        }
    }

    private function validateBoolean(string $field, mixed $value): void
    {
        // Une valeur est booléenne si elle est 0, 1, '0', '1', true, false, 'on', 'off', null
        if ($value !== null && !in_array(strtolower((string)$value), ['0', '1', 'true', 'false', 'on', 'off'], true)) {
            $this->addError($field, "Le champ '{$field}' doit être une valeur booléenne valide (ex: 0, 1, true, false, on, off).");
        }
    }

    private function validateRequiredIf(string $field, mixed $value, string $otherField, string $otherValue): void
    {
        // Vérifie si l'autre champ existe dans les données et a la valeur spécifiée
        if (isset($this->data[$otherField]) && (string)$this->data[$otherField] === $otherValue) {
            // Si la condition est remplie, alors ce champ est requis
            if (empty($value) && $value !== 0 && $value !== '0') {
                $this->addError($field, "Le champ '{$field}' est requis lorsque '{$otherField}' est '{$otherValue}'.");
            }
        }
    }

    private function validateAfterOrEqual(string $field, ?string $value, string $param): void
    {
        if (!empty($value)) {
            try {
                $dateValue = new \DateTime($value);
                $dateParam = new \DateTime($param); // Peut aussi être un autre champ: $this->data[$param]
                if ($dateValue < $dateParam) {
                    $this->addError($field, "Le champ '{$field}' doit être une date égale ou postérieure à '{$param}'.");
                }
            } catch (\Exception $e) {
                $this->addError($field, "Le format de date du champ '{$field}' ou de la règle est invalide.");
            }
        }
    }

    private function validateBeforeOrEqual(string $field, ?string $value, string $param): void
    {
        if (!empty($value)) {
            try {
                $dateValue = new \DateTime($value);
                $dateParam = new \DateTime($param); // Peut aussi être un autre champ: $this->data[$param]
                if ($dateValue > $dateParam) {
                    $this->addError($field, "Le champ '{$field}' doit être une date égale ou antérieure à '{$param}'.");
                }
            } catch (\Exception $e) {
                $this->addError($field, "Le format de date du champ '{$field}' ou de la règle est invalide.");
            }
        }
    }
}