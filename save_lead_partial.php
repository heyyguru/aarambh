<?php
define('AARAMBH_INIT', true);
require_once __DIR__ . '/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid method']);
    exit;
}

if (!RateLimiter::checkLimit('lead_creation', 5, 3600)) {
    jsonResponse(['success' => false, 'message' => 'Too many registration attempts. Please try again later.'], 429);
}

$name = InputValidator::validateName($_POST['student_name'] ?? '');
$email = InputValidator::validateEmail($_POST['email'] ?? '');
$phone = InputValidator::validatePhone($_POST['phone'] ?? '');
$class = InputValidator::validateAlphaNumSpace($_POST['student_class'] ?? '', 50);

if (!$name || !$email || !$phone || !$class) {
    jsonResponse(['success' => false, 'message' => 'Invalid data format. Please use valid name, email and phone.'], 400);
}

try {
    $db = getDB();
    
    // Check if this session already has a pending lead
    $stmt = $db->prepare("SELECT id FROM students WHERE phone = ? OR email = ? ORDER BY created_at DESC LIMIT 1");
    $stmt->execute([$phone, $email]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
        // Update existing lead
        $stmt = $db->prepare("UPDATE students SET name = ?, email = ?, phone = ?, student_class = ? WHERE id = ? AND status = 'lead'");
        $stmt->execute([
            $name ?: null,
            $email ?: null,
            $phone ?: null,
            $class ?: null,
            $existing['id']
        ]);
        echo json_encode(['success' => true, 'action' => 'updated']);
    } else {
        // Insert new lead
        $stmt = $db->prepare("INSERT INTO students (name, email, phone, student_class, status) VALUES (?, ?, ?, ?, 'lead')");
        $stmt->execute([
            $name ?: 'Unknown',
            $email ?: null,
            $phone ?: null,
            $class ?: null
        ]);
        echo json_encode(['success' => true, 'action' => 'inserted']);
    }

} catch (Exception $e) {
    error_log("Partial Lead Save Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
