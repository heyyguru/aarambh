<?php
/**
 * =====================================================
 * AARAMBH by HeyyGuru — Configuration
 * Domain: aarambh.heyyguru.in
 * =====================================================
 */

// Prevent direct access
if (!defined('AARAMBH_INIT') && basename($_SERVER['PHP_SELF']) === 'config.php') {
    http_response_code(403);
    exit('Direct access forbidden.');
}

// ---------------------------------------------------
// Parse .env File
// ---------------------------------------------------
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);
            if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
                putenv(sprintf('%s=%s', $name, $value));
                $_ENV[$name] = $value;
                $_SERVER[$name] = $value;
            }
        }
    }
}

// ---------------------------------------------------
// Database Configuration
// ---------------------------------------------------
define('DB_HOST', getenv('DB_HOST'));
define('DB_NAME', getenv('DB_NAME'));
define('DB_USER', getenv('DB_USER'));
define('DB_PASS', getenv('DB_PASS'));
define('DB_CHARSET', 'utf8mb4');

// ---------------------------------------------------
// Razorpay Configuration
// ---------------------------------------------------
define('RAZORPAY_KEY_ID', getenv('RAZORPAY_KEY_ID'));
define('RAZORPAY_KEY_SECRET', getenv('RAZORPAY_KEY_SECRET'));

// ---------------------------------------------------
// Email / SMTP Configuration
// ---------------------------------------------------
define('SMTP_HOST', getenv('SMTP_HOST'));
define('SMTP_PORT', getenv('SMTP_PORT'));
define('SMTP_USERNAME', getenv('SMTP_USERNAME'));
define('SMTP_PASSWORD', getenv('SMTP_PASSWORD'));
define('SMTP_FROM_EMAIL', getenv('SMTP_FROM_EMAIL'));
define('SMTP_FROM_NAME', getenv('SMTP_FROM_NAME'));
define('SMTP_ENCRYPTION', 'tls');

// ---------------------------------------------------
// Site Configuration
// ---------------------------------------------------
define('SITE_URL', 'https://aarambh.heyyguru.in');
define('SITE_NAME', 'Aarambh by HeyyGuru');
define('COURSE_NAME', 'AARAMBH');
define('COURSE_PRICE', 19);
define('COURSE_PRICE_PAISE', 1900);
define('COURSE_DURATION', '6 Days');
define('SUPPORT_EMAIL', 'academics@heyyguru.in');
define('SUPPORT_PHONE', '7676798650');

// ---------------------------------------------------
// Fake Counter Configuration
// ---------------------------------------------------
define('COUNTER_BASE', 2847);                    // Starting base count
define('COUNTER_LAUNCH_DATE', '2026-06-15');     // Launch date for time-based increment
define('COUNTER_DAILY_INCREMENT', 37);           // Average daily increase
define('COUNTER_HOURLY_VARIATION', 5);           // Random hourly variation

// ---------------------------------------------------
// Admin Configuration
// ---------------------------------------------------
define('ADMIN_SESSION_NAME', 'aarambh_admin_session');
define('ADMIN_SESSION_TIMEOUT', 3600); // 1 hour

// ---------------------------------------------------
// Database Connection (PDO)
// ---------------------------------------------------
function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            throw $e; // Let callers handle DB failure gracefully
        }
    }
    return $pdo;
}

// ---------------------------------------------------
// Helper Functions
// ---------------------------------------------------
function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function jsonResponse($data, $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function getClientIP() {
    $keys = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'REMOTE_ADDR'];
    foreach ($keys as $key) {
        if (!empty($_SERVER[$key])) {
            $ip = explode(',', $_SERVER[$key]);
            return trim($ip[0]);
        }
    }
    return '0.0.0.0';
}

function isValidPhone($phone) {
    return preg_match('/^[6-9]\d{9}$/', $phone);
}

function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}
