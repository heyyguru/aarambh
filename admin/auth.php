<?php
/**
 * JWT Authentication Provider
 */
if (!defined('AARAMBH_INIT')) exit;

define('JWT_SECRET', getenv('JWT_SECRET') ?: 'aarambh_super_secret_key_2026_!@#');
define('JWT_ACCESS_EXP', 900); // 15 minutes
define('JWT_REFRESH_EXP', 604800); // 7 days

function base64url_encode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function base64url_decode($data) {
    return base64_decode(strtr($data, '-_', '+/') . str_repeat('=', 3 - (3 + strlen($data)) % 4));
}

function create_jwt($payload, $secret = JWT_SECRET) {
    $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
    $payload = json_encode($payload);

    $base64UrlHeader = base64url_encode($header);
    $base64UrlPayload = base64url_encode($payload);

    $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $secret, true);
    $base64UrlSignature = base64url_encode($signature);

    return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
}

function verify_jwt($jwt, $secret = JWT_SECRET) {
    if (empty($jwt)) return false;
    
    $tokenParts = explode('.', $jwt);
    if (count($tokenParts) != 3) return false;

    $header = base64url_decode($tokenParts[0]);
    $payload = base64url_decode($tokenParts[1]);
    $signature_provided = $tokenParts[2];

    $base64UrlHeader = base64url_encode($header);
    $base64UrlPayload = base64url_encode($payload);
    $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $secret, true);
    $base64UrlSignature = base64url_encode($signature);

    if (hash_equals($base64UrlSignature, $signature_provided)) {
        $data = json_decode($payload, true);
        if (isset($data['exp']) && $data['exp'] < time()) {
            return false; // Expired
        }
        return $data;
    }
    return false;
}

function require_admin_auth() {
    $accessToken = $_COOKIE['admin_access_token'] ?? '';
    $refreshToken = $_COOKIE['admin_refresh_token'] ?? '';

    if ($accessToken) {
        $payload = verify_jwt($accessToken);
        if ($payload && $payload['type'] === 'access') {
            return $payload['username'];
        }
    }

    // Access token invalid or expired. Check refresh token.
    if ($refreshToken) {
        $payload = verify_jwt($refreshToken);
        if ($payload && $payload['type'] === 'refresh') {
            $username = $payload['username'];
            
            // Issue new access token
            $newAccess = create_jwt([
                'type' => 'access',
                'username' => $username,
                'exp' => time() + JWT_ACCESS_EXP
            ]);
            
            // Set cookie (HttpOnly)
            setcookie('admin_access_token', $newAccess, time() + JWT_ACCESS_EXP, '/', '', false, true);
            
            return $username;
        }
    }

    // Not authenticated at all
    header('Location: login.php?expired=1');
    exit;
}
