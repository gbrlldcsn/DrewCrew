<?php
if (!function_exists('validate_required')) {
    function validate_required($value, string $label, array &$errors): void {
        if (trim((string)$value) === '') {
            $errors[] = "$label is required.";
        }
    }
}

if (!function_exists('validate_email')) {
    function validate_email($value, string $label, array &$errors): void {
        if (trim((string)$value) === '' || !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "$label must be a valid email address.";
        }
    }
}

if (!function_exists('validate_min_length')) {
    function validate_min_length($value, int $min, string $label, array &$errors): void {
        if (strlen((string)$value) < $min) {
            $errors[] = "$label must be at least $min characters.";
        }
    }
}

if (!function_exists('validate_numeric')) {
    function validate_numeric($value, string $label, array &$errors): void {
        if (!is_numeric($value)) {
            $errors[] = "$label must be numeric.";
        }
    }
}

if (!function_exists('validate_positive')) {
    function validate_positive($value, string $label, array &$errors): void {
        if (!is_numeric($value) || (float)$value < 0) {
            $errors[] = "$label must be zero or a positive number.";
        }
    }
}


