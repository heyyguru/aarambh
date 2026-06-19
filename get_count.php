<?php
/**
 * Get Enrollment Count — AJAX Endpoint
 * Returns fake enrollment count that increases over time
 */
define('AARAMBH_INIT', true);
require_once __DIR__ . '/config.php';

header('Content-Type: application/json');

$now = time();
$launch = strtotime(COUNTER_LAUNCH_DATE);
$daysSinceLaunch = max(0, floor(($now - $launch) / 86400));
$hourOfDay = (int) date('G');

// Base count + daily growth + hourly variation
$dailyGrowth = $daysSinceLaunch * COUNTER_DAILY_INCREMENT;
$hourlyVar = floor(sin($hourOfDay * 0.5) * COUNTER_HOURLY_VARIATION + COUNTER_HOURLY_VARIATION);
$minuteVar = floor((int) date('i') / 10);

$totalCount = COUNTER_BASE + $dailyGrowth + $hourlyVar + $minuteVar;

// Also add real paid students count
try {
    $db = getDB();
    $stmt = $db->query("SELECT COUNT(*) as real_count FROM students WHERE status IN ('paid', 'enrolled')");
    $real = $stmt->fetch();
    $totalCount += intval($real['real_count'] ?? 0);
} catch (Exception $e) {
    // Ignore DB errors for counter
}

jsonResponse([
    'success' => true,
    'count' => $totalCount,
    'formatted' => number_format($totalCount)
]);
