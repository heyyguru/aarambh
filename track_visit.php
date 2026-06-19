<?php
/**
 * Track Visit — AJAX Endpoint
 * Logs page visits for analytics
 */
define('AARAMBH_INIT', true);
require_once __DIR__ . '/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Invalid request method.'], 405);
}

try {
    $db = getDB();

    $stmt = $db->prepare("INSERT INTO page_visits (ip_address, user_agent, referrer, page_url, utm_source, utm_medium, utm_campaign) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        getClientIP(),
        $_SERVER['HTTP_USER_AGENT'] ?? '',
        $_SERVER['HTTP_REFERER'] ?? '',
        $_POST['page_url'] ?? '/',
        $_POST['utm_source'] ?? null,
        $_POST['utm_medium'] ?? null,
        $_POST['utm_campaign'] ?? null
    ]);

    jsonResponse(['success' => true]);

} catch (Exception $e) {
    error_log("Track visit error: " . $e->getMessage());
    jsonResponse(['success' => true]); // Don't fail silently for analytics
}
