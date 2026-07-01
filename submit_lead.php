<?php
/**
 * Submit Lead — AJAX Endpoint
 * Saves student registration data to database
 */
define('AARAMBH_INIT', true);
require_once __DIR__ . '/config.php';

header('Content-Type: application/json');

// Only POST allowed
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Invalid request method.'], 405);
}

if (!RateLimiter::checkLimit('lead_creation', 5, 3600)) {
    jsonResponse(['success' => false, 'message' => 'Too many registration attempts. Please try again later.'], 429);
}

// Get & sanitize input
$name = InputValidator::validateName($_POST['student_name'] ?? '') ?: 'Student';
$email = InputValidator::validateEmail($_POST['email'] ?? '') ?: 'noemail@example.com';
$phone = InputValidator::validatePhone($_POST['phone'] ?? '');
$studentClass = InputValidator::validateAlphaNumSpace($_POST['student_class'] ?? '', 50);
$city = InputValidator::validateAlphaNumSpace($_POST['city'] ?? '', 100);
$utmSource = InputValidator::validateAlphaNumSpace($_POST['utm_source'] ?? '', 100);
$utmMedium = InputValidator::validateAlphaNumSpace($_POST['utm_medium'] ?? '', 100);
$utmCampaign = InputValidator::validateAlphaNumSpace($_POST['utm_campaign'] ?? '', 100);
$utmContent = InputValidator::validateAlphaNumSpace($_POST['utm_content'] ?? '', 100);

if (!$phone || !$studentClass) {
    jsonResponse(['success' => false, 'message' => 'Invalid or missing required fields. Please ensure you provide a valid 10-digit phone number.'], 400);
}

try {
    $db = getDB();

    // Check if phone already registered
    $stmt = $db->prepare("SELECT id, status FROM students WHERE phone = ?");
    $stmt->execute([$phone]);
    $existing = $stmt->fetch();

    if ($existing) {
        // If already paid, inform user
        if ($existing['status'] === 'paid' || $existing['status'] === 'enrolled') {
            jsonResponse(['success' => false, 'message' => 'This phone number is already enrolled! Check your email for class details.'], 400);
        }

        // Update existing lead
        $stmt = $db->prepare("UPDATE students SET name = ?, email = ?, student_class = ?, city = ?, utm_source = ?, utm_medium = ?, utm_campaign = ?, utm_content = ?, ip_address = ?, user_agent = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([
            $name, $email, $studentClass, $city,
            $utmSource, $utmMedium, $utmCampaign, $utmContent,
            getClientIP(), $_SERVER['HTTP_USER_AGENT'] ?? '',
            $existing['id']
        ]);

        jsonResponse([
            'success' => true,
            'student_id' => $existing['id'],
            'message' => 'Details updated. Proceeding to payment.'
        ]);
    }

    // Insert new lead
    $stmt = $db->prepare("INSERT INTO students (name, email, phone, student_class, city, status, utm_source, utm_medium, utm_campaign, utm_content, ip_address, user_agent) VALUES (?, ?, ?, ?, ?, 'lead', ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $name, $email, $phone, $studentClass, $city,
        $utmSource, $utmMedium, $utmCampaign, $utmContent,
        getClientIP(), $_SERVER['HTTP_USER_AGENT'] ?? ''
    ]);

    $studentId = $db->lastInsertId();

    jsonResponse([
        'success' => true,
        'student_id' => $studentId,
        'message' => 'Registration successful. Proceeding to payment.'
    ]);

} catch (PDOException $e) {
    error_log("Submit lead error: " . $e->getMessage());

    // Handle duplicate email gracefully
    if ($e->getCode() == 23000) {
        jsonResponse(['success' => false, 'message' => 'This phone number is already registered. Please use a different number or contact support.'], 400);
    }

    jsonResponse(['success' => false, 'message' => 'Server error. Please try again later.'], 500);
}
