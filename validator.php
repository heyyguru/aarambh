<?php
/**
 * Strict Input Validation and Sanitization
 * AARAMBH by HeyyGuru
 */
if (!defined('AARAMBH_INIT')) exit;

class InputValidator {
    /**
     * Validate and sanitize string (strip HTML, limit length)
     */
    public static function validateString($input, $maxLength = 255) {
        if (!is_string($input)) return '';
        $input = trim($input);
        if (mb_strlen($input) > $maxLength) return '';
        return htmlspecialchars(strip_tags($input), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Validate name (letters, spaces, dots, hyphens), strict length
     */
    public static function validateName($input, $maxLength = 100) {
        if (!is_string($input)) return false;
        $input = trim($input);
        if (empty($input) || mb_strlen($input) > $maxLength) return false;
        if (!preg_match('/^[a-zA-Z\s\.\-]+$/', $input)) return false;
        return $input; // Return raw, as it is safe to store (only safe chars)
    }

    /**
     * Validate alphanumeric string (letters, numbers, spaces, dots, hyphens)
     */
    public static function validateAlphaNumSpace($input, $maxLength = 100) {
        if (!is_string($input)) return false;
        $input = trim($input);
        if (empty($input) || mb_strlen($input) > $maxLength) return false;
        if (!preg_match('/^[a-zA-Z0-9\s\.\-]+$/', $input)) return false;
        return $input;
    }

    /**
     * Validate strictly alphanumeric (no spaces)
     */
    public static function validateAlphaNum($input, $maxLength = 100) {
        if (!is_string($input)) return false;
        $input = trim($input);
        if (empty($input) || mb_strlen($input) > $maxLength) return false;
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $input)) return false;
        return $input;
    }

    /**
     * Validate Indian phone number
     */
    public static function validatePhone($input) {
        if (!is_string($input) && !is_numeric($input)) return false;
        $input = trim((string)$input);
        if (preg_match('/^[6-9]\d{9}$/', $input)) return $input;
        return false;
    }

    /**
     * Validate email
     */
    public static function validateEmail($input, $maxLength = 255) {
        if (!is_string($input)) return false;
        $input = trim($input);
        if (mb_strlen($input) > $maxLength) return false;
        return filter_var($input, FILTER_VALIDATE_EMAIL);
    }

    /**
     * Validate positive integer
     */
    public static function validateInt($input, $min = 1) {
        $val = filter_var($input, FILTER_VALIDATE_INT);
        if ($val === false || $val < $min) return false;
        return $val;
    }

    /**
     * Validate against allowed list
     */
    public static function validateEnum($input, array $allowed) {
        if (!is_string($input)) return false;
        $input = trim($input);
        if (in_array($input, $allowed, true)) return $input;
        return false;
    }

    /**
     * Validate URL
     */
    public static function validateUrl($input, $maxLength = 2048) {
        if (!is_string($input)) return false;
        $input = trim($input);
        if (mb_strlen($input) > $maxLength) return false;
        // Accept relative paths (like /page) or absolute URLs
        if (preg_match('/^\/[a-zA-Z0-9\-_\/\.\?\=]*$/', $input)) return $input;
        return filter_var($input, FILTER_VALIDATE_URL) ? $input : false;
    }

    /**
     * Validate Razorpay ID (alphanumeric, underscores, hyphens)
     */
    public static function validateRazorpayId($input, $maxLength = 128) {
        if (!is_string($input)) return false;
        $input = trim($input);
        if (empty($input) || mb_strlen($input) > $maxLength) return false;
        if (!preg_match('/^[a-zA-Z0-9_\-]+$/', $input)) return false;
        return $input;
    }
}
