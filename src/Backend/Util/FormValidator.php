<?php

namespace App\Backend\Util;

class FormValidator
{
    private array $data;
    private array $errors;
    private array $rules;

    public function __construct()
    {
        $this->data = [];
        $this->errors = [];
        $this->rules = [];
    }

    public function validate(array $data, array $rules): void
    {
        $this->data = $data;
        $this->rules = $rules;
        $this->errors = [];

        foreach ($this->rules as $field => $fieldRules) {
            $value = $this->data[$field] ?? null;
            $rulesArray = explode('|', $fieldRules);

            foreach ($rulesArray as $rule) {
                list($ruleName, $param) = array_pad(explode(':', $rule, 2), 2, null);

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
                    case 'minlength':
                        $this->validateMinLength($field, $value, $param);
                        break;
                    case 'max':
                    case 'maxlength':
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
                        list($otherField, $otherValue) = array_pad(explode(',', $param, 2), 2, null);
                        $this->validateRequiredIf($field, $value, $otherField, $otherValue);
                        break;
                    case 'after_or_equal':
                        $this->validateAfterOrEqual($field, $value, $param);
                        break;
                    case 'before_or_equal':
                        $this->validateBeforeOrEqual($field, $value, $param);
                        break;
                    case 'length':
                        $this->validateLength($field, $value, $param);
                        break;
                }
                if (isset($this->errors[$field]) && in_array("Le champ '{$field}' est requis.", $this->errors[$field]) && empty($value) && $value !== 0 && $value !== '0') {
                    break;
                }
            }
        }
    }

    private function addError(string $field, string $message): void
    {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }
        $this->errors[$field][] = $message;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function isValid(): bool
    {
        return empty($this->errors);
    }

    private function validateRequired(string $field, mixed $value): void
    {
        if (empty($value) && $value !== 0 && $value !== '0') {
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
        if ($value !== null && !is_string($value)) {
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
            if (!($d && $d->format('Y-m-d') === $value)) {
                $this->addError($field, "Le champ '{$field}' doit être une date valide (YYYY-MM-DD).");
            }
        }
    }

    private function validateSame(string $field, mixed $value, string $param): void
    {
        if (isset($this->data[$param]) && $value !== $this->data[$param]) {
            $this->addError($field, "Le champ '{$field}' doit correspondre au champ '{$param}'.");
        }
    }

    private function validateIn(string $field, ?string $value, string $param): void
    {
        $choices = array_map('trim', explode(',', $param));
        if (!empty($value) && !in_array($value, $choices)) {
            $this->addError($field, "La valeur du champ '{$field}' n'est pas valide.");
        }
    }

    private function validateBoolean(string $field, mixed $value): void
    {
        if ($value !== null && !in_array(strtolower((string)$value), ['0', '1', 'true', 'false', 'on', 'off'], true)) {
            $this->addError($field, "Le champ '{$field}' doit être une valeur booléenne valide (ex: 0, 1, true, false, on, off).");
        }
    }

    private function validateRequiredIf(string $field, mixed $value, string $otherField, string $otherValue): void
    {
        if (isset($this->data[$otherField]) && (string)$this->data[$otherField] === $otherValue) {
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
                $dateParam = new \DateTime($param);
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
                $dateParam = new \DateTime($param);
                if ($dateValue > $dateParam) {
                    $this->addError($field, "Le champ '{$field}' doit être une date égale ou antérieure à '{$param}'.");
                }
            } catch (\Exception $e) {
                $this->addError($field, "Le format de date du champ '{$field}' ou de la règle est invalide.");
            }
        }
    }
}