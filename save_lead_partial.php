<?php
require_once __DIR__ . '/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid method']);
    exit;
}

$name = trim($_POST['student_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$class = trim($_POST['student_class'] ?? '');
$session_id = session_id();

// We need at least phone or email to track usefully
if (empty($phone) && empty($email)) {
    echo json_encode(['success' => false, 'message' => 'No contact info']);
    exit;
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
