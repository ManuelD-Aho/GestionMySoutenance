<?php
// src/Backend/Util/FormValidator.php

namespace App\Backend\Util;

class FormValidator
{
    private array $data;
    private array $errors = [];

    /**
     * Valide un ensemble de données par rapport à un ensemble de règles.
     * @param array $data Les données à valider (ex: $_POST).
     * @param array $rules Les règles de validation (ex: ['email' => 'required|email']).
     * @return bool True si toutes les données sont valides, false sinon.
     */
    public function validate(array $data, array $rules): bool
    {
        $this->data = $data;
        $this->errors = [];

        foreach ($rules as $field => $fieldRules) {
            $value = $data[$field] ?? null;
            $rulesArray = explode('|', $fieldRules);

            foreach ($rulesArray as $rule) {
                // Permet d'avoir des paramètres dans les règles, ex: min:8
                [$ruleName, $param] = array_pad(explode(':', $rule, 2), 2, null);

                // Si le champ est requis et vide, on arrête la validation pour ce champ.
                if ($ruleName === 'required' && ($value === null || $value === '')) {
                    $this->addError($field, "Le champ '{$field}' est requis.");
                    break; // Passe au champ suivant
                }

                // Si le champ n'est pas requis et est vide, on ne valide pas les autres règles.
                if ($value === null || $value === '') continue;

                $methodName = 'validate' . ucfirst($ruleName);
                if (method_exists($this, $methodName)) {
                    $this->{$methodName}($field, $value, $param);
                }
            }
        }
        return empty($this->errors);
    }

    public function getErrors(): array { return $this->errors; }
    public function isValid(): bool { return empty($this->errors); }

    private function addError(string $field, string $message): void
    {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = $message;
        }
    }

    // --- Règles de validation ---
    private function validateRequired(string $field, $value): void { /* Géré dans la boucle principale */ }
    private function validateEmail(string $field, $value): void { if (!filter_var($value, FILTER_VALIDATE_EMAIL)) $this->addError($field, "Le format de l'email est invalide."); }
    private function validateMin(string $field, $value, $param): void { if (strlen($value) < (int)$param) $this->addError($field, "Le champ '{$field}' doit contenir au moins {$param} caractères."); }
    private function validateMax(string $field, $value, $param): void { if (strlen($value) > (int)$param) $this->addError($field, "Le champ '{$field}' ne doit pas dépasser {$param} caractères."); }
    private function validateNumeric(string $field, $value): void { if (!is_numeric($value)) $this->addError($field, "Le champ '{$field}' doit être une valeur numérique."); }
    private function validateSame(string $field, $value, $param): void { if (!isset($this->data[$param]) || $value !== $this->data[$param]) $this->addError($field, "Le champ '{$field}' doit correspondre au champ '{$param}'."); }
    private function validateIn(string $field, $value, $param): void { if (!in_array($value, explode(',', $param))) $this->addError($field, "La valeur du champ '{$field}' n'est pas autorisée."); }
}