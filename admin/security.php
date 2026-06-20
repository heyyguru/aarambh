<?php
/**
 * Security Module — CSRF, Rate Limiting, Secure Cookies
 * AARAMBH by HeyyGuru
 */
if (!defined('AARAMBH_INIT')) exit;

// ---------------------------------------------------
// Session Management (required for CSRF tokens)
// ---------------------------------------------------
function init_secure_session() {
    if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params([
            'lifetime' => 0,
            'path'     => '/admin/',
            'domain'   => '',
            'secure'   => true,
            'httponly'  => true,
            'samesite'  => 'Strict',
        ]);
        session_name('aarambh_admin_sess');
        session_start();
    }

    // Regenerate session ID periodically to prevent fixation
    if (!isset($_SESSION['_created'])) {
        $_SESSION['_created'] = time();
    } elseif (time() - $_SESSION['_created'] > 1800) { // 30 minutes
        session_regenerate_id(true);
        $_SESSION['_created'] = time();
    }
}

// ---------------------------------------------------
// CSRF Protection
// ---------------------------------------------------
function generate_csrf_token() {
    init_secure_session();
    if (empty($_SESSION['csrf_token']) || empty($_SESSION['csrf_token_time'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['csrf_token_time'] = time();
    }
    // Rotate token every 60 minutes
    if (time() - $_SESSION['csrf_token_time'] > 3600) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['csrf_token_time'] = time();
    }
    return $_SESSION['csrf_token'];
}

function validate_csrf_token($token) {
    init_secure_session();
    if (empty($token) || empty($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

function csrf_hidden_field() {
    $token = generate_csrf_token();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
}

// ---------------------------------------------------
// Login Rate Limiting
// ---------------------------------------------------
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCKOUT_DURATION', 900); // 15 minutes in seconds

function check_rate_limit($ip, $username = null) {
    try {
        $db = getDB();
        $since = date('Y-m-d H:i:s', time() - LOCKOUT_DURATION);

        // Check by IP
        $stmt = $db->prepare(
            "SELECT COUNT(*) as attempts FROM login_attempts 
             WHERE ip_address = ? AND attempted_at > ? AND success = 0"
        );
        $stmt->execute([$ip, $since]);
        $result = $stmt->fetch();

        if ($result['attempts'] >= MAX_LOGIN_ATTEMPTS) {
            return false; // Rate limited
        }

        return true; // OK to proceed
    } catch (Exception $e) {
        error_log("Rate limit check error: " . $e->getMessage());
        return true; // Fail open — don't lock out on DB errors
    }
}

function record_login_attempt($ip, $username, $success = false) {
    try {
        $db = getDB();
        $stmt = $db->prepare(
            "INSERT INTO login_attempts (ip_address, username, attempted_at, success) 
             VALUES (?, ?, NOW(), ?)"
        );
        $stmt->execute([$ip, $username, $success ? 1 : 0]);
    } catch (Exception $e) {
        error_log("Record login attempt error: " . $e->getMessage());
    }
}

function get_remaining_lockout_time($ip) {
    try {
        $db = getDB();
        $stmt = $db->prepare(
            "SELECT MAX(attempted_at) as last_attempt FROM login_attempts 
             WHERE ip_address = ? AND success = 0"
        );
        $stmt->execute([$ip]);
        $result = $stmt->fetch();

        if ($result && $result['last_attempt']) {
            $lastAttempt = strtotime($result['last_attempt']);
            $unlockTime = $lastAttempt + LOCKOUT_DURATION;
            $remaining = $unlockTime - time();
            return max(0, $remaining);
        }
    } catch (Exception $e) {
        error_log("Lockout time check error: " . $e->getMessage());
    }
    return 0;
}

function clear_login_attempts($ip) {
    try {
        $db = getDB();
        $stmt = $db->prepare("DELETE FROM login_attempts WHERE ip_address = ?");
        $stmt->execute([$ip]);
    } catch (Exception $e) {
        error_log("Clear login attempts error: " . $e->getMessage());
    }
}

// ---------------------------------------------------
// Secure Cookie Helper
// ---------------------------------------------------
function set_secure_cookie($name, $value, $maxAge) {
    setcookie($name, $value, [
        'expires'  => time() + $maxAge,
        'path'     => '/admin/',
        'domain'   => '',
        'secure'   => true,
        'httponly'  => true,
        'samesite' => 'Strict',
    ]);
}

function delete_secure_cookie($name) {
    setcookie($name, '', [
        'expires'  => time() - 3600,
        'path'     => '/admin/',
        'domain'   => '',
        'secure'   => true,
        'httponly'  => true,
        'samesite' => 'Strict',
    ]);
}
