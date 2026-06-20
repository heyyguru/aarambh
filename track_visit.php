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

if (!RateLimiter::checkLimit('analytics', 30, 60)) {
    // Silently fail for analytics spam to save resources and DB writes
    exit;
}

try {
    $db = getDB();

    $pageUrl = InputValidator::validateUrl($_POST['page_url'] ?? '/');
    $utmSource = InputValidator::validateAlphaNumSpace($_POST['utm_source'] ?? '', 100);
    $utmMedium = InputValidator::validateAlphaNumSpace($_POST['utm_medium'] ?? '', 100);
    $utmCampaign = InputValidator::validateAlphaNumSpace($_POST['utm_campaign'] ?? '', 100);

    $stmt = $db->prepare("INSERT INTO page_visits (ip_address, user_agent, referrer, page_url, utm_source, utm_medium, utm_campaign) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        getClientIP(),
        InputValidator::validateString($_SERVER['HTTP_USER_AGENT'] ?? '', 255),
        InputValidator::validateUrl($_SERVER['HTTP_REFERER'] ?? '', 255),
        $pageUrl ?: '/',
        $utmSource ?: null,
        $utmMedium ?: null,
        $utmCampaign ?: null
    ]);

    jsonResponse(['success' => true]);

} catch (Exception $e) {
    error_log("Track visit error: " . $e->getMessage());
    jsonResponse(['success' => true]); // Don't fail silently for analytics
}
