<?php
/**
 * Create Razorpay Order — AJAX Endpoint
 * Creates a Razorpay order for ₹19 payment
 */
define('AARAMBH_INIT', true);
require_once __DIR__ . '/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Invalid request method.'], 405);
}

if (!RateLimiter::checkLimit('payments', 15, 3600)) {
    jsonResponse(['success' => false, 'message' => 'Too many payment requests. Please try again later.'], 429);
}

// Get input
$input = json_decode(file_get_contents('php://input'), true);
$studentId = InputValidator::validateInt($input['student_id'] ?? 0);

if (!$studentId) {
    jsonResponse(['success' => false, 'message' => 'Invalid student ID.'], 400);
}

try {
    $db = getDB();

    // Verify student exists
    $stmt = $db->prepare("SELECT id, name, email, phone FROM students WHERE id = ?");
    $stmt->execute([$studentId]);
    $student = $stmt->fetch();

    if (!$student) {
        jsonResponse(['success' => false, 'message' => 'Student not found. Please register again.'], 404);
    }

    // Create Razorpay order via API
    $orderData = [
        'amount' => COURSE_PRICE_PAISE,
        'currency' => 'INR',
        'receipt' => 'aarambh_' . $studentId . '_' . time(),
        'notes' => [
            'student_id' => $studentId,
            'student_name' => $student['name'],
            'course' => COURSE_NAME,
            'email' => $student['email'],
            'phone' => $student['phone']
        ]
    ];

    $ch = curl_init('https://api.razorpay.com/v1/orders');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($orderData),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json'
        ],
        CURLOPT_USERPWD => RAZORPAY_KEY_ID . ':' . RAZORPAY_KEY_SECRET,
        CURLOPT_TIMEOUT => 30,
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($curlError) {
        error_log("Razorpay CURL error: " . $curlError);
        jsonResponse(['success' => false, 'message' => 'Payment gateway error. Please try again.'], 500);
    }

    $order = json_decode($response, true);

    if ($httpCode !== 200 || empty($order['id'])) {
        error_log("Razorpay order creation failed: " . $response);
        jsonResponse(['success' => false, 'message' => 'Failed to create payment order. Please try again.'], 500);
    }

    // Save order to database
    $stmt = $db->prepare("INSERT INTO payments (student_id, razorpay_order_id, amount, status) VALUES (?, ?, ?, 'created')");
    $stmt->execute([$studentId, $order['id'], COURSE_PRICE]);

    // Update student status
    $stmt = $db->prepare("UPDATE students SET razorpay_order_id = ?, status = 'payment_initiated' WHERE id = ?");
    $stmt->execute([$order['id'], $studentId]);

    jsonResponse([
        'success' => true,
        'order_id' => $order['id'],
        'amount' => COURSE_PRICE_PAISE,
        'currency' => 'INR',
        'student_name' => $student['name'],
        'student_email' => $student['email'],
        'student_phone' => $student['phone']
    ]);

} catch (Exception $e) {
    error_log("Create order error: " . $e->getMessage());
    jsonResponse(['success' => false, 'message' => 'Server error. Please try again.'], 500);
}
