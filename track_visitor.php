<?php
// track_visitor.php
require_once 'config/db.php';

header('Content-Type: application/json');

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

// Get JSON data
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['phone']) || !isset($data['student_class'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$phone = trim($data['phone']);
$student_class = trim($data['student_class']);

$utm_source = trim($data['utm_source'] ?? '');
$utm_medium = trim($data['utm_medium'] ?? '');
$utm_campaign = trim($data['utm_campaign'] ?? '');

if (empty($utm_source) && isset($_SERVER['HTTP_REFERER'])) {
    $referer = strtolower($_SERVER['HTTP_REFERER']);
    if (strpos($referer, 'instagram.com') !== false) {
        $utm_source = 'instagram';
    } elseif (strpos($referer, 'facebook.com') !== false) {
        $utm_source = 'facebook';
    }
}

// Optional fields
$name = isset($data['name']) && !empty(trim($data['name'])) ? trim($data['name']) : 'Visitor_' . substr($phone, -4);
$email = isset($data['email']) && !empty(trim($data['email'])) ? trim($data['email']) : 'visitor' . time() . '@example.com';
$status = 'visitor'; // Indicates they haven't paid/completed enrollment

try {
    // Check if phone number already exists
    $stmt = $pdo->prepare("SELECT id FROM students WHERE phone = ?");
    $stmt->execute([$phone]);
    $existing = $stmt->fetch();

    if ($existing) {
        // Update existing record's class, but leave status alone if they already paid
        $updateStmt = $pdo->prepare("UPDATE students SET student_class = ?, utm_source = COALESCE(NULLIF(utm_source, ''), ?), utm_medium = COALESCE(NULLIF(utm_medium, ''), ?), utm_campaign = COALESCE(NULLIF(utm_campaign, ''), ?), updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        $updateStmt->execute([$student_class, $utm_source, $utm_medium, $utm_campaign, $existing['id']]);
        echo json_encode(['success' => true, 'message' => 'Visitor updated']);
    } else {
        // Insert new partial lead
        $insertStmt = $pdo->prepare("
            INSERT INTO students (name, email, phone, student_class, status, amount, utm_source, utm_medium, utm_campaign) 
            VALUES (?, ?, ?, ?, ?, 0, ?, ?, ?)
        ");
        $insertStmt->execute([$name, $email, $phone, $student_class, $status, $utm_source, $utm_medium, $utm_campaign]);
        echo json_encode(['success' => true, 'message' => 'Visitor tracked']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
