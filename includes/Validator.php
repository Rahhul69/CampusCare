<?php
class Validator {
    private $errors = [];

    public function validate($data, $rules) {
        foreach ($rules as $field => $rule) {
            $value = $data[$field] ?? '';
            
            foreach ($rule as $validation => $param) {
                switch ($validation) {
                    case 'required':
                        if (empty($value)) {
                            $this->errors[$field] = ucfirst($field) . " is required";
                        }
                        break;
                    case 'email':
                        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                            $this->errors[$field] = "Invalid email format";
                        }
                        break;
                    case 'min':
                        if (strlen($value) < $param) {
                            $this->errors[$field] = ucfirst($field) . " must be at least $param characters";
                        }
                        break;
                    case 'match':
                        if ($value !== $data[$param]) {
                            $this->errors[$field] = "Passwords do not match";
                        }
                        break;
                    case 'pattern':
                        if (!preg_match($param, $value)) {
                            $this->errors[$field] = "Invalid " . ucfirst($field) . " format";
                        }
                        break;
                }
            }
        }
        return empty($this->errors);
    }

    public function getErrors() {
        return $this->errors;
    }
}